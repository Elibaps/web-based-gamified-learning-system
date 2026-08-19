// story/game/main.js
// Phaser-based Story Mode minimal playable vertical slice

window.addEventListener('DOMContentLoaded', () => {
  // ensure the page-level HUD elements exist
  const username = window.STORY_USERNAME || 'Player';
  const userLevel = window.STORY_LEVEL || 1;
  const userExp = window.STORY_EXP || 0;
  const userCoins = window.STORY_COINS || 0;

  const config = {
    type: Phaser.AUTO,
    parent: 'gameContainer',
    width: Math.max(800, Math.floor(window.innerWidth * 0.88)),
    height: Math.max(600, Math.floor(window.innerHeight * 0.88)),
    physics: {
      default: 'arcade',
      arcade: { debug: false }
    },
    scene: {
      preload: preload,
      create: create,
      update: update
    }
  };

  const game = new Phaser.Game(config);

  let player, cursors, keys, npcs = [], interactables = [], obstacles;
  let currentInteract = null;
  let hud = {};

  function preload() {
    // Use existing site assets where possible
    this.load.image('player', 'images/player.png');
    this.load.image('boss', 'images/boss.png');
    this.load.image('npc', 'images/boss.png');
    this.load.image('tile', 'images/css.png'); // placeholder
  }

  function create() {
    const scene = this;

    // Create a simple background
    this.add.rectangle(0, 0, config.width * 2, config.height * 2, 0x1f2937).setOrigin(0);

    // Obstacles group
    obstacles = this.physics.add.staticGroup();
    for (let i = 0; i < 12; i++) {
      const x = 200 + (i % 6) * 120;
      const y = 150 + Math.floor(i / 6) * 180;
      const tile = this.add.image(x, y, 'tile').setDisplaySize(96, 96).setTint(0x0ea5a4);
      obstacles.add(tile);
      tile.setData('type', 'obstacle');
    }

    // Player
    player = this.physics.add.sprite(400, 400, 'player');
    player.setCollideWorldBounds(true);
    player.body.setSize(32, 32);
    player.setScale(0.7);
    player.speed = 160;
    player.facing = 'down';

    // Collide player with obstacles
    this.physics.add.collider(player, obstacles);

    // NPC example (story NPC)
    const storyNpc = this.physics.add.staticSprite(600, 350, 'npc').setScale(0.9);
    storyNpc.name = 'Elder Ada';
    storyNpc.id = 'npc_story_1';
    storyNpc.setData('dialogue', [
      "Welcome, coder. The mainframe's subsystems are failing.",
      "Can you help by repairing the broken terminal near the plaza?"
    ]);
    npcs.push(storyNpc);

    // Shop NPC
    const shopNpc = this.physics.add.staticSprite(200, 600, 'npc').setScale(0.9).setTint(0xffb4c6);
    shopNpc.name = 'Merchant Lin';
    shopNpc.id = 'npc_shop_1';
    shopNpc.setData('shop', true);
    npcs.push(shopNpc);

    // Dungeon entrance
    const dungeonEntrance = this.physics.add.staticSprite(1000, 400, 'boss').setScale(0.8).setTint(0xff944d);
    dungeonEntrance.name = 'Dungeon Entrance';
    dungeonEntrance.id = 'dungeon_1';
    dungeonEntrance.setData('dungeon', true);
    npcs.push(dungeonEntrance);

    // Broken terminal interactable
    const terminal = this.physics.add.staticSprite(500, 500, 'tile').setScale(0.9).setTint(0xf472b6);
    terminal.id = 'village_terminal_01';
    terminal.setData('broken', true);
    terminal.setData('topic', 'HTML');
    interactables.push(terminal);

    // Collisions between player and NPCs (optional physical blocking)
    npcs.forEach(n => this.physics.add.collider(player, n));

    // Input
    cursors = this.input.keyboard.createCursorKeys();
    keys = this.input.keyboard.addKeys({ W: Phaser.Input.Keyboard.KeyCodes.W, A: Phaser.Input.Keyboard.KeyCodes.A, S: Phaser.Input.Keyboard.KeyCodes.S, D: Phaser.Input.Keyboard.KeyCodes.D, E: Phaser.Input.Keyboard.KeyCodes.E });

    // Camera follow
    this.cameras.main.setBounds(0, 0, config.width * 2, config.height * 2);
    this.physics.world.setBounds(0, 0, config.width * 2, config.height * 2);
    this.cameras.main.startFollow(player, true, 0.08, 0.08);

    // HUD references
    hud.username = document.getElementById('hudUsername');
    hud.level = document.getElementById('hudLevel');
    hud.xp = document.getElementById('hudXP');
    hud.coins = document.getElementById('hudCoins');
    hud.objective = document.getElementById('hudObjective');

    // initialize HUD values (from server-injected globals)
    if (hud.username) hud.username.textContent = username;
    if (hud.level) hud.level.textContent = `Level ${userLevel}`;
    if (hud.xp) hud.xp.textContent = `XP ${userExp}`;
    if (hud.coins) hud.coins.textContent = `${userCoins} ⍟`;
    if (hud.objective) hud.objective.textContent = `Objective: Talk to ${storyNpc.name}`;

    // Interaction prompt element
    const prompt = document.getElementById('interactionPrompt');
    prompt.style.display = 'none';

    // Periodic proximity checks
    this.time.addEvent({ delay: 150, loop: true, callback: () => {
      const closest = findClosestInteractable(player, npcs.concat(interactables));
      if (closest && Phaser.Math.Distance.Between(player.x, player.y, closest.x, closest.y) < 80) {
        currentInteract = closest;
        prompt.style.display = 'block';
        prompt.textContent = 'Press E to interact';
      } else {
        currentInteract = null;
        prompt.style.display = 'none';
      }
    }});

    // E key handler
    this.input.keyboard.on('keydown-E', async () => {
      if (!currentInteract) return;
      if (currentInteract.getData('dialogue')) {
        openDialogue(currentInteract.getData('dialogue'), currentInteract.name);
      } else if (currentInteract.getData('shop')) {
        openShop();
      } else if (currentInteract.getData('dungeon')) {
        // Navigate to battle for dungeon boss
        window.location.href = `battle.php?topic=JavaScript&return=story`;
      } else if (currentInteract.getData('broken')) {
        // Open coding challenge modal
        openChallenge(currentInteract);
      }
    });

    // Load saved story state from server to update interactables
    fetch('story/story_state.php')
      .then(r => r.json())
      .then(data => {
        if (Array.isArray(data.objects)) {
          data.objects.forEach(obj => {
            if (obj.object_key === 'village_terminal_01' && obj.state === 'repaired') {
              terminal.setTint(0x65a30d); // repaired green
              terminal.setData('broken', false);
            }
          });
        }
      }).catch(err => { console.warn('Could not load story state', err); });
  }

  function update(time, delta) {
    const speed = player.speed;
    let vx = 0, vy = 0;
    if (cursors.left.isDown || keys.A.isDown) { vx = -speed; player.facing = 'left'; }
    else if (cursors.right.isDown || keys.D.isDown) { vx = speed; player.facing = 'right'; }
    if (cursors.up.isDown || keys.W.isDown) { vy = -speed; player.facing = 'up'; }
    else if (cursors.down.isDown || keys.S.isDown) { vy = speed; player.facing = 'down'; }

    player.setVelocity(vx, vy);

    // Basic facing visual cue (flipX)
    if (player.facing === 'left') player.setFlipX(true);
    else if (player.facing === 'right') player.setFlipX(false);
  }

  function findClosestInteractable(playerObj, list) {
    let closest = null; let best = Infinity;
    list.forEach(o => {
      if (!o || !o.x) return;
      const d = Phaser.Math.Distance.Between(playerObj.x, playerObj.y, o.x, o.y);
      if (d < best) { best = d; closest = o; }
    });
    return closest;
  }

  // Dialogue overlay functions (DOM)
  function openDialogue(lines, speaker) {
    const dlg = document.getElementById('dialogueBox');
    const dlgSpeaker = document.getElementById('dialogueSpeaker');
    const dlgText = document.getElementById('dialogueText');
    let idx = 0;
    dlgSpeaker.textContent = speaker || '';
    dlgText.textContent = lines[idx];
    dlg.style.display = 'block';
    const nextBtn = document.getElementById('dialogueNext');
    nextBtn.onclick = () => {
      idx++;
      if (idx >= lines.length) {
        dlg.style.display = 'none';
        // after initial story dialogue, set objective
        if (speaker === 'Elder Ada') {
          const obj = document.getElementById('hudObjective');
          if (obj) obj.textContent = 'Objective: Repair the broken terminal near the plaza.';
        }
      } else {
        dlgText.textContent = lines[idx];
      }
    };
  }

  // Challenge modal
  async function openChallenge(interactable) {
    if (!interactable.getData('broken')) {
      const info = document.getElementById('notification'); info.textContent = 'This object is already repaired.'; setTimeout(()=>info.textContent='',2000); return;
    }
    const modal = document.getElementById('challengeModal');
    const qText = document.getElementById('challengeQuestion');
    const ansInput = document.getElementById('challengeAnswer');
    const submitBtn = document.getElementById('challengeSubmit');

    // Fetch one question from get_questions.php for the topic
    const topic = interactable.getData('topic') || 'HTML';
    let questions = [];
    try {
      const res = await fetch(`get_questions.php?topic=${encodeURIComponent(topic)}`);
      questions = await res.json();
    } catch (e) { console.error('Could not fetch questions', e); }

    if (!questions || !questions.length) {
      qText.textContent = 'No questions available for ' + topic;
      return;
    }
    const q = questions[0];
    qText.textContent = q.question_text;
    ansInput.value = '';
    modal.style.display = 'block';

    submitBtn.onclick = async () => {
      const attempt = ansInput.value.trim().toLowerCase();
      const correct = (q.answer || '').trim().toLowerCase();
      if (attempt === correct) {
        modal.style.display = 'none';
        // Award XP and coins via award_xp.php
        try {
          const post = new URLSearchParams();
          post.set('xp', 20);
          post.set('course', topic);
          post.set('lesson_slug', 'practice');
          post.set('coins', 10);
          const resp = await fetch('award_xp.php', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: post.toString() });
          const data = await resp.json();
          // Update HUD coins if present
          if (data && typeof data.newCoins !== 'undefined' && hud.coins) hud.coins.textContent = data.newCoins + ' ⍟';
        } catch (e) { console.warn('Award XP failed', e); }

        // Persist story object repaired
        try {
          await fetch('story/story_state.php', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: `object_key=${encodeURIComponent(interactable.id)}&state=repaired` });
        } catch (e) { console.warn('Could not persist story object', e); }

        // Visual change
        interactable.setData('broken', false);
        interactable.setTint(0x65a30d);
        // Notify
        const info = document.getElementById('notification'); info.textContent = 'Repaired! +20 XP +10 ⍟'; setTimeout(()=>info.textContent='',3000);

      } else {
        const info = document.getElementById('notification'); info.textContent = 'Incorrect. Try again.'; setTimeout(()=>info.textContent='',2000);
      }
    };
  }

  // Shop overlay
  function openShop() {
    const modal = document.getElementById('shopModal');
    const coinsEl = document.getElementById('shopCoins');
    coinsEl.textContent = hud.coins ? hud.coins.textContent : '0';
    modal.style.display = 'block';
    // bind buy buttons
    document.querySelectorAll('.shopBuy').forEach(btn => {
      btn.onclick = async () => {
        const key = btn.dataset.key;
        try {
          const resp = await fetch('story/shop_purchase.php', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: `item_key=${encodeURIComponent(key)}&quantity=1` });
          const data = await resp.json();
          if (data.success) {
            if (hud.coins) hud.coins.textContent = data.newCoins + ' ⍟';
            const info = document.getElementById('notification'); info.textContent = 'Purchased ' + key; setTimeout(()=>info.textContent='',2000);
          } else {
            const info = document.getElementById('notification'); info.textContent = data.error || 'Purchase failed'; setTimeout(()=>info.textContent='',2000);
          }
        } catch (e) { console.error(e); }
      };
    });
  }

});
