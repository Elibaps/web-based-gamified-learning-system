CREATE DATABASE IF NOT EXISTS `codenest`;
USE `codenest`;

-- ============================================================
-- USERS
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
  `user_id`  int(11)      NOT NULL AUTO_INCREMENT,
  `username` varchar(50)  NOT NULL,
  `email`    varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `level`    int(11)      NOT NULL DEFAULT 1,
  `exp`      int(11)      NOT NULL DEFAULT 0,
  `coins`    int(11)      NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email`    (`email`)
);

-- ============================================================
-- LESSONS  (course content stored in DB, not hardcoded)
-- ============================================================
CREATE TABLE IF NOT EXISTS `lessons` (
  `lesson_id`  int(11)      NOT NULL AUTO_INCREMENT,
  `course`     varchar(50)  NOT NULL,
  `slug`       varchar(50)  NOT NULL,
  `title`      varchar(100) NOT NULL,
  `content`    text         NOT NULL,
  `sort_order` int(11)      NOT NULL DEFAULT 0,
  PRIMARY KEY (`lesson_id`),
  UNIQUE KEY `course_slug` (`course`, `slug`)
);

-- ============================================================
-- QUESTIONS  (per-language battle / quiz questions)
-- ============================================================
CREATE TABLE IF NOT EXISTS `questions` (
  `question_id`   int(11)      NOT NULL AUTO_INCREMENT,
  `course`        varchar(50)  NOT NULL,
  `question_text` varchar(255) NOT NULL,
  `answer`        varchar(100) NOT NULL,
  PRIMARY KEY (`question_id`),
  KEY `idx_course` (`course`)
);

-- ============================================================
-- USER PROGRESS  (which lessons each user has completed)
-- ============================================================
CREATE TABLE IF NOT EXISTS `user_progress` (
  `progress_id`  int(11)     NOT NULL AUTO_INCREMENT,
  `user_id`      int(11)     NOT NULL,
  `course`       varchar(50) NOT NULL,
  `lesson_slug`  varchar(50) NOT NULL,
  `completed_at` datetime    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`progress_id`),
  UNIQUE KEY `user_lesson` (`user_id`, `course`, `lesson_slug`),
  CONSTRAINT `fk_progress_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
);

-- ============================================================
-- SEED: default user (password = "123")
-- ============================================================
INSERT IGNORE INTO `users` (`username`, `email`, `password`, `level`, `exp`, `coins`)
VALUES ('123', 'elijah@gmail.com',
        '$2y$10$84uegFmUCirOQwxHWJPj8Ofj9S473XzfhLVPPy3tAbkRG6uD1UzSS', 1, 0, 0);

-- ============================================================
-- SEED: HTML lessons
-- ============================================================
INSERT IGNORE INTO `lessons` (`course`, `slug`, `title`, `content`, `sort_order`) VALUES
('HTML', 'intro', 'Introduction',
 '<h1>Introduction to HTML</h1>
  <p>HTML (HyperText Markup Language) is the foundation of every website.
  It defines the structure and content of web pages using elements called <strong>tags</strong>.</p>
  <p>Every webpage you visit is built with HTML. It tells the browser what to display &mdash;
  headings, paragraphs, images, links, and more.</p>', 1),

('HTML', 'basics', 'HTML Basics',
 '<h1>HTML Basics</h1>
  <p>HTML documents are made up of <strong>elements</strong>. Each element has an opening tag,
  content, and a closing tag.</p>
  <div class="code-block">&lt;h1&gt;Hello, World!&lt;/h1&gt;<br>
  &lt;p&gt;This is a paragraph.&lt;/p&gt;<br>
  &lt;a href="#"&gt;This is a link&lt;/a&gt;</div>
  <p>Some tags are self-closing, like <code>&lt;img&gt;</code> and <code>&lt;br&gt;</code>.</p>', 2),

('HTML', 'syntax', 'Tags & Syntax',
 '<h1>HTML Tags &amp; Syntax</h1>
  <p>Here are the most common HTML tags you will use:</p>
  <ul>
    <li><strong>&lt;h1&gt; &ndash; &lt;h6&gt;</strong> &mdash; Headings (largest to smallest)</li>
    <li><strong>&lt;p&gt;</strong> &mdash; Paragraph</li>
    <li><strong>&lt;a href=""&gt;</strong> &mdash; Anchor / Link</li>
    <li><strong>&lt;img src=""&gt;</strong> &mdash; Image</li>
    <li><strong>&lt;div&gt;</strong> &mdash; Generic container</li>
    <li><strong>&lt;ul&gt; / &lt;ol&gt; / &lt;li&gt;</strong> &mdash; Lists</li>
  </ul>', 3),

('HTML', 'practice', 'Practice',
 '<h1>Practice HTML</h1>
  <p>Ready to test your HTML skills? Each correct answer in battle attacks the enemy.
  Answer faster to deal more damage!</p>', 4),

('HTML', 'quiz', 'Quiz',
 '<h1>HTML Quiz</h1>
  <p>Test your HTML knowledge with a full quiz. Good luck!</p>', 5);

-- ============================================================
-- SEED: CSS lessons
-- ============================================================
INSERT IGNORE INTO `lessons` (`course`, `slug`, `title`, `content`, `sort_order`) VALUES
('CSS', 'intro', 'Introduction',
 '<h1>Introduction to CSS</h1>
  <p>CSS (Cascading Style Sheets) controls the <strong>visual appearance</strong> of HTML elements.
  Without CSS, websites would be plain black-and-white text.</p>
  <p>CSS lets you change colors, fonts, layouts, spacing, animations, and much more.</p>', 1),

('CSS', 'basics', 'CSS Basics',
 '<h1>CSS Basics</h1>
  <p>CSS is written as a set of rules. Each rule has a <strong>selector</strong> and
  one or more <strong>declarations</strong>.</p>
  <div class="code-block">body {<br>
  &nbsp;&nbsp;background: #111;<br>
  &nbsp;&nbsp;color: white;<br>
  &nbsp;&nbsp;font-family: sans-serif;<br>
  }</div>', 2),

('CSS', 'syntax', 'Tags & Syntax',
 '<h1>CSS Properties</h1>
  <p>Commonly used CSS properties:</p>
  <ul>
    <li><strong>color</strong> &mdash; Text color</li>
    <li><strong>background</strong> &mdash; Background color or image</li>
    <li><strong>padding</strong> &mdash; Space inside an element</li>
    <li><strong>margin</strong> &mdash; Space outside an element</li>
    <li><strong>display</strong> &mdash; Layout type (block, flex, grid)</li>
    <li><strong>border</strong> &mdash; Element border</li>
    <li><strong>border-radius</strong> &mdash; Rounded corners</li>
  </ul>', 3),

('CSS', 'practice', 'Practice',
 '<h1>Practice CSS</h1>
  <p>Put your CSS knowledge to the test in a coding battle!</p>', 4),

('CSS', 'quiz', 'Quiz',
 '<h1>CSS Quiz</h1>
  <p>Take the CSS quiz to measure your styling skills!</p>', 5);

-- ============================================================
-- SEED: JavaScript lessons
-- ============================================================
INSERT IGNORE INTO `lessons` (`course`, `slug`, `title`, `content`, `sort_order`) VALUES
('JavaScript', 'intro', 'Introduction',
 '<h1>Introduction to JavaScript</h1>
  <p>JavaScript is a <strong>programming language</strong> that makes websites interactive.
  It runs directly in the browser and can respond to user actions like clicks, inputs, and scrolls.</p>
  <p>JavaScript is one of the three core technologies of the web, alongside HTML and CSS.</p>', 1),

('JavaScript', 'basics', 'JS Basics',
 '<h1>JavaScript Basics</h1>
  <p>Variables store data. Functions perform actions. Here is a basic example:</p>
  <div class="code-block">let name = "CodeNest";<br>
  alert("Welcome to " + name);<br><br>
  function greet(user) {<br>
  &nbsp;&nbsp;console.log("Hello, " + user);<br>
  }<br>
  greet("Player");</div>', 2),

('JavaScript', 'syntax', 'Tags & Syntax',
 '<h1>JavaScript Syntax</h1>
  <ul>
    <li><strong>let / const / var</strong> &mdash; Declare variables</li>
    <li><strong>function</strong> &mdash; Define reusable blocks of code</li>
    <li><strong>if / else</strong> &mdash; Conditionals</li>
    <li><strong>for / while</strong> &mdash; Loops</li>
    <li><strong>document.getElementById()</strong> &mdash; Select a DOM element</li>
    <li><strong>addEventListener()</strong> &mdash; Listen for user events</li>
  </ul>', 3),

('JavaScript', 'practice', 'Practice',
 '<h1>Practice JavaScript</h1>
  <p>Put your JavaScript knowledge to the test in a coding battle!</p>', 4),

('JavaScript', 'quiz', 'Quiz',
 '<h1>JavaScript Quiz</h1>
  <p>Test your JavaScript skills with our quiz challenge!</p>', 5);

-- ============================================================
-- SEED: PHP lessons
-- ============================================================
INSERT IGNORE INTO `lessons` (`course`, `slug`, `title`, `content`, `sort_order`) VALUES
('PHP', 'intro', 'Introduction',
 '<h1>Introduction to PHP</h1>
  <p>PHP is a server-side scripting language used to build dynamic web applications.
  It runs on the server and sends HTML to the browser.</p>
  <p>PHP powers over 75% of all websites, including WordPress and Facebook (historically).</p>', 1),

('PHP', 'basics', 'PHP Basics',
 '<h1>PHP Basics</h1>
  <p>PHP code is embedded inside HTML using <code>&lt;?php ... ?&gt;</code> tags.</p>
  <div class="code-block">&lt;?php<br>
  &nbsp;&nbsp;$name = "CodeNest";<br>
  &nbsp;&nbsp;echo "Hello, $name!";<br>
  ?&gt;</div>', 2),

('PHP', 'syntax', 'Tags & Syntax',
 '<h1>PHP Syntax</h1>
  <ul>
    <li><strong>$variable</strong> &mdash; Variable declaration</li>
    <li><strong>echo</strong> &mdash; Output text to the browser</li>
    <li><strong>include / require</strong> &mdash; Import other PHP files</li>
    <li><strong>$_GET / $_POST</strong> &mdash; Read form data</li>
    <li><strong>$_SESSION</strong> &mdash; Store session data</li>
    <li><strong>password_hash()</strong> &mdash; Hash passwords securely</li>
  </ul>', 3),

('PHP', 'practice', 'Practice',
 '<h1>Practice PHP</h1>
  <p>Challenge yourself with PHP battle questions to reinforce your backend skills!</p>', 4),

('PHP', 'quiz', 'Quiz',
 '<h1>PHP Quiz</h1>
  <p>Test your PHP knowledge with our quiz!</p>', 5);

-- ============================================================
-- SEED: Questions — HTML
-- ============================================================
INSERT IGNORE INTO `questions` (`course`, `question_text`, `answer`) VALUES
('HTML', 'What does HTML stand for?',             'hypertext markup language'),
('HTML', 'What tag creates the largest heading?', 'h1'),
('HTML', 'What tag is used for links?',           'a'),
('HTML', 'What tag embeds an image?',             'img'),
('HTML', 'What tag creates a paragraph?',         'p'),
('HTML', 'What attribute sets a link destination?', 'href'),
('HTML', 'What tag creates an unordered list?',   'ul'),
('HTML', 'What tag creates a list item?',         'li'),
('HTML', 'What tag creates a line break?',        'br'),
('HTML', 'What tag is used for bold text?',       'strong');

-- ============================================================
-- SEED: Questions — CSS
-- ============================================================
INSERT IGNORE INTO `questions` (`course`, `question_text`, `answer`) VALUES
('CSS', 'What does CSS stand for?',                       'cascading style sheets'),
('CSS', 'What property changes text color?',              'color'),
('CSS', 'What property adds space inside an element?',    'padding'),
('CSS', 'What property adds space outside an element?',   'margin'),
('CSS', 'What display value enables flexbox?',            'flex'),
('CSS', 'What property changes the font?',                'font-family'),
('CSS', 'What property sets element width?',              'width'),
('CSS', 'What value hides an element (display)?',         'none'),
('CSS', 'What property rounds element corners?',          'border-radius'),
('CSS', 'What property controls element transparency?',   'opacity');

-- ============================================================
-- SEED: Questions — JavaScript
-- ============================================================
INSERT IGNORE INTO `questions` (`course`, `question_text`, `answer`) VALUES
('JavaScript', 'What function shows a popup message?',       'alert'),
('JavaScript', 'What keyword declares a constant?',          'const'),
('JavaScript', 'What method adds an item to an array?',      'push'),
('JavaScript', 'What does DOM stand for?',                   'document object model'),
('JavaScript', 'What keyword declares a block variable?',    'let'),
('JavaScript', 'What operator checks strict equality?',      '==='),
('JavaScript', 'What method selects an element by ID?',      'getelementbyid'),
('JavaScript', 'What method logs output to the console?',    'log'),
('JavaScript', 'What event fires on a button click?',        'click'),
('JavaScript', 'What method removes the last array item?',   'pop');

-- ============================================================
-- SEED: Questions — PHP
-- ============================================================
INSERT IGNORE INTO `questions` (`course`, `question_text`, `answer`) VALUES
('PHP', 'What symbol starts a PHP variable?',          '$'),
('PHP', 'What keyword outputs text in PHP?',           'echo'),
('PHP', 'What superglobal holds POST form data?',      '$_post'),
('PHP', 'What function hashes a password?',            'password_hash'),
('PHP', 'What function starts a session?',             'session_start'),
('PHP', 'What does PHP stand for?',                    'php hypertext preprocessor'),
('PHP', 'What superglobal stores session data?',       '$_session'),
('PHP', 'What function connects to MySQL?',            'mysqli_connect'),
('PHP', 'What keyword imports another PHP file?',      'include'),
('PHP', 'What function redirects the browser in PHP?', 'header');