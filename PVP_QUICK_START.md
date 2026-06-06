# Real-Time PvP System - Quick Start Guide

## ✅ Installation Complete!

The real-time PvP system has been fully implemented. Here's what was created:

### Files Created/Modified

1. **pvp_setup.php** (3.5 KB)
   - Database initialization script
   - Creates all necessary PvP tables
   - Run this FIRST before testing

2. **pvp_queue.php** (4.2 KB)
   - Waiting lobby while searching for opponent
   - Shows queue position and timer
   - Polls for match every 1 second

3. **pvp_match_api.php** (12.5 KB)
   - Backend API for all matchmaking operations
   - Endpoints: join_queue, check_queue, submit_answer, get_progress, etc.
   - Server-side match logic and scoring

4. **pvp_match.php** (11.6 KB)
   - Live match page where players compete
   - Real-time opponent progress (1 second polling)
   - Shows both players' scores and progress bars
   - Handles question display and answer submission

5. **pvp.php** (2.9 KB) - MODIFIED
   - Landing page with "Find Opponent" button
   - Replaced bot battle logic with matchmaking system
   - Simple, clean interface

6. **pvp_admin.php** (8.5 KB)
   - Admin panel for monitoring PvP system
   - Shows queue status, active matches, recent games
   - Includes data reset utility for testing

7. **PVP_SYSTEM_README.md** (8.5 KB)
   - Comprehensive documentation
   - Architecture details, API reference
   - Troubleshooting guide

## 🚀 Getting Started

### Step 1: Initialize Database

Visit in your browser:
```
http://localhost/codenest.worktrees/agents-battle-transition-and-learning-path/pvp_setup.php
```

Expected output: `✓ PvP tables created successfully!`

### Step 2: Test the System

**Single Player Test:**
1. Go to: `http://localhost/codenest.worktrees/agents-battle-transition-and-learning-path/pvp.php`
2. Click "🔍 Find Opponent"
3. You'll see "Waiting for opponent..." in `pvp_queue.php`

**Two Player Test (Recommended):**
1. Open browser window/tab #1 with username: `123` (or any user)
2. Open browser window/tab #2 with DIFFERENT username
3. Both click "🔍 Find Opponent" within a few seconds of each other
4. After matchmaking (2 second check), both redirected to `pvp_match.php`
5. Both answer same 5 questions
6. First to 5 correct wins! 🏆

### Step 3: Monitor System

Visit admin panel:
```
http://localhost/codenest.worktrees/agents-battle-transition-and-learning-path/pvp_admin.php
```
(Only accessible to admin users)

## 📊 System Architecture

```
User A                          User B
   ↓                               ↓
pvp.php ← Find Opponent button → pvp.php
   ↓                               ↓
pvp_queue.php ← Matchmaking → pvp_queue.php
   ↓           (2 sec check)       ↓
   ← ← ← ← Matched! ← ← ← ← 
   ↓                               ↓
pvp_match.php ← → pvp_match_api.php ← → pvp_match.php
   |            (1 sec polling)       |
   | Answer Q1  → Database ← Answer Q1|
   | Answer Q2  → Database ← Answer Q2|
   | ... (score tracking)            |
   ↓                               ↓
Winner! +50 XP              Loser (no XP)
```

## 🎮 Match Rules

- **5 Questions per match** (random from database)
- **First to 5 correct answers wins**
- **Winner gets 50 XP**
- **Real-time opponent tracking**
- **1 second polling interval** for updates

## 🔧 Key Features

✅ **Real-time matchmaking** - Players matched within 2 seconds
✅ **Live score updates** - Opponent progress visible in real-time
✅ **Synchronized questions** - Both players answer identical questions
✅ **Automatic winner detection** - Match ends when someone hits 5 correct
✅ **XP reward system** - Winners automatically get 50 XP
✅ **Queue monitoring** - See position in queue
✅ **Admin dashboard** - Monitor all matches and queue status
✅ **Polling-based** - No WebSocket dependency, works on all servers
✅ **Database persistence** - All match history stored

## 📁 Database Tables Created

```sql
pvp_queue            -- Players waiting for matches
pvp_matches          -- Active and completed matches
match_questions      -- Questions for each match
player_answers       -- Individual player answers
```

## 🧪 Testing Checklist

- [ ] Database initialized successfully (visit pvp_setup.php)
- [ ] PvP page loads without errors (visit pvp.php)
- [ ] Can join queue (click "Find Opponent")
- [ ] Queue page shows countdown (pvp_queue.php)
- [ ] Admin panel accessible (pvp_admin.php - admin only)
- [ ] Two players can match together
- [ ] Both see same questions
- [ ] Answers recorded correctly
- [ ] Winner determined after 5 correct
- [ ] Winner gets +50 XP notification
- [ ] Can see opponent's real-time progress

## ⚙️ Configuration

### Change Win Condition (currently 5)
Edit `pvp_match_api.php`, line ~210:
```php
if ($updated_match['player1_score'] >= 5) {
```
Change `5` to any number.

### Change XP Reward (currently 50)
Edit `pvp_match_api.php`, line ~215:
```php
$conn->query("UPDATE users SET exp = exp + 50 WHERE user_id = $user_id");
```
Change `50` to desired amount.

### Change Polling Interval (currently 1 second)
Edit `pvp_queue.php` and `pvp_match.php`:
```javascript
setInterval(checkForMatch, 1000)  // 1000 = 1 second
```
Change `1000` (milliseconds) to desired interval.

## 🐛 Troubleshooting

**"Match not found" error:**
- Ensure database tables exist (run pvp_setup.php)
- Check both players are using different accounts
- Wait 2-3 seconds for matchmaking to occur

**Opponent not visible in match:**
- Check browser console for JavaScript errors
- Verify API is returning valid data
- Try refreshing the page

**Scores not updating:**
- Check network tab in DevTools for API calls
- Verify database is storing answers correctly
- Check browser console for errors

**Players see different matches:**
- This shouldn't happen - verify database integrity
- Run pvp_admin.php to check table status
- Reset data if needed (pvp_admin.php → Clear All PvP Data)

## 📝 API Endpoints

All endpoints in `pvp_match_api.php`:

| Action | Method | Parameters | Purpose |
|--------|--------|-----------|---------|
| join_queue | POST | - | Join matchmaking queue |
| check_queue | GET | user_id | Check if matched |
| leave_queue | POST | user_id | Leave queue |
| submit_answer | POST | match_id, player_id, question_id, is_correct | Submit answer |
| get_progress | GET | match_id, player_id | Get opponent progress |
| get_match | GET | match_id | Get match details |
| end_match | POST | match_id | End the match |

## 📞 Support Files

- **PVP_SYSTEM_README.md** - Full documentation
- **pvp_admin.php** - System monitoring and debugging
- **pvp_setup.php** - Database initialization

## ✨ Next Steps

1. ✅ Initialize database (pvp_setup.php)
2. ✅ Test with single player (go to pvp.php, click Find Opponent)
3. ✅ Test with two players (in different browsers/tabs)
4. ✅ Monitor with admin panel (pvp_admin.php)
5. ✅ Check match history in admin dashboard

---

**System Ready! Happy battles! ⚔️**
