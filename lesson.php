<?php
session_start();

if(!isset($_SESSION['username'])){
    header("Location: login.html");
    exit();
}

$course = $_GET['course'];
$lesson = isset($_GET['lesson'])
? $_GET['lesson']
: 'intro';
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $course; ?> Lessons</title>
    <link rel="stylesheet" href="UI.css">
</head>

<body class="dashboard-page">

<div class="navbar">

  <div class="nav-left">
    <span class="logo-text">🪙 CodeNest</span>
  </div>

  <div class="nav-right">
    <a href="dashboard.php" class="logout-btn">
      ← Back
    </a>
  </div>

</div>

<div class="lesson-page">

<div class="lesson-sidebar">

    <h2><?php echo $course; ?></h2>

    <a
    href="lesson.php?course=<?php echo $course; ?>&lesson=intro"
    class="lesson-item active">

    Introduction

    </a>

    <a
    href="lesson.php?course=<?php echo $course; ?>&lesson=basics"
    class="lesson-item">

    Basics

    </a>

    <a
    href="lesson.php?course=<?php echo $course; ?>&lesson=syntax"
    class="lesson-item">

    Tags & Syntax

    </a>

    <a
    href="lesson.php?course=<?php echo $course; ?>&lesson=practice"
    class="lesson-item">

    Practice

    </a>

    <a
    href="lesson.php?course=<?php echo $course; ?>&lesson=quiz"
    class="lesson-item">

    Quiz

    </a>

</div>

  <div class="lesson-content">

<?php

if($course == "HTML"){

  if($lesson == "intro"){

    echo "
    <h1>Introduction to HTML</h1>

    <p>
    HTML is the structure of every website.
    </p>
    ";

  }

  if($lesson == "basics"){

    echo "
    <h1>HTML Basics</h1>

    <div class='code-block'>
    &lt;h1&gt;Hello&lt;/h1&gt;
    </div>
    ";

  }

  if($lesson == "syntax"){

    echo "
    <h1>HTML Tags</h1>

    <ul>
      <li>&lt;p&gt;</li>
      <li>&lt;img&gt;</li>
      <li>&lt;a&gt;</li>
    </ul>
    ";

  }

  if($lesson == "practice"){

    echo "
    <h1>Practice</h1>

    <p>Create your first webpage.</p>

    <button onclick=\"startBattle('HTML')\">
      Start Practice Battle
    </button>
    ";

  }

  if($lesson == "quiz"){

    echo "
    <h1>HTML Quiz</h1>

    <button onclick=\"startQuiz('HTML')\">
      Start Quiz
    </button>
    ";

  }

}
if($course == "CSS"){

    echo "
    <h1>CSS Basics</h1>

    <p>
      CSS is used for styling websites.
    </p>

    <h2>Example CSS</h2>

    <div class='code-block'>
body {<br>
&nbsp;&nbsp;background: black;<br>
&nbsp;&nbsp;color: white;<br>
}
    </div>

    <h2>What CSS Can Do</h2>

    <ul>
      <li>Colors</li>
      <li>Layouts</li>
      <li>Animations</li>
      <li>Responsive Design</li>
    </ul>

    <button onclick=\"startQuiz('CSS')\">
      Take Quiz
    </button>
    ";
}

if($course == "JavaScript"){

    echo "
    <h1>JavaScript Basics</h1>

    <p>
      JavaScript adds interactivity to websites.
    </p>

    <h2>Example JavaScript</h2>

    <div class='code-block'>
alert('Hello World');
    </div>

    <h2>JavaScript Features</h2>

    <ul>
      <li>Buttons</li>
      <li>Games</li>
      <li>Animations</li>
      <li>Logic Systems</li>
    </ul>

    <button onclick=\"startQuiz('JavaScript')\">
      Take Quiz
    </button>
    ";
}

?>

  </div>

</div>

<script>
function startQuiz(language){

  localStorage.setItem(
    "quizLanguage",
    language
  );

  window.location.href = "quiz.php";
}
</script>

</body>
</html>