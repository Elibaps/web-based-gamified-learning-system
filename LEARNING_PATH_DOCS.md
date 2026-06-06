# Learning Path System Documentation

## Overview
The Learning Path system guides users through lessons sequentially, creating a structured learning journey from HTML → CSS → JavaScript → PHP → Advanced JavaScript. Each lesson must be completed before the next one unlocks.

## Files Created

### 1. **learning_path.php**
Main page displaying the visual roadmap of all lessons in order.

**Features:**
- Visual RPG-style progression map with nodes
- Overall completion percentage with progress bar
- Each lesson shown as a node with status:
  - 🔒 Locked (prerequisites not met)
  - ⭐ Current (available to start)
  - ✅ Completed (finished)
- Time estimate for each lesson
- Next lesson preview card
- Click to start/resume lessons
- Responsive design

**URL:** `learning_path.php?path_id=1`

### 2. **learning_path_api.php**
RESTful API for managing learning paths and user progress.

**Endpoints:**

#### GET `/learning_path_api.php?action=get_paths`
Returns all available learning paths
```json
{
  "success": true,
  "paths": [
    {
      "path_id": 1,
      "path_name": "Web Development Fundamentals",
      "path_description": "...",
      "total_lessons": 5,
      "completed_lessons": 0,
      "total_time": 140
    }
  ]
}
```

#### GET `/learning_path_api.php?action=get_path_progress&path_id=1&user_id=123`
Gets detailed progress for a user on a specific path
```json
{
  "success": true,
  "path_id": 1,
  "lessons": [
    {
      "path_lesson_id": 1,
      "lesson_order": 1,
      "course": "HTML",
      "lesson_slug": "intro",
      "time_estimate": 20,
      "locked": false,
      "completed": false,
      "completion_percentage": 0,
      "title": "HTML Basics"
    }
  ],
  "overall_progress": {
    "completed": 0,
    "total": 5,
    "percentage": 0
  }
}
```

#### POST `/learning_path_api.php?action=unlock_next`
Marks current lesson as complete and unlocks the next one
**Required POST parameters:**
- `path_lesson_id`: ID of the lesson that was completed

```json
{
  "success": true,
  "message": "Next lesson unlocked",
  "next_path_lesson_id": 2
}
```

#### GET `/learning_path_api.php?action=check_prerequisites&path_lesson_id=1&user_id=123`
Checks if user can unlock a specific lesson
```json
{
  "success": true,
  "can_unlock": true,
  "lesson_order": 1
}
```

### 3. **Modified quiz.php**
Enhanced to unlock the next lesson when a quiz is passed (score > 70%).

**Behavior:**
- When quiz ends with score > 70%, automatically calls `learning_path_api.php?action=unlock_next`
- Finds the current lesson in the path and marks it completed
- Unlocks the next lesson in sequence
- Shows "Next Lesson Unlocked!" message

### 4. **Modified lesson.php**
Shows learning path progress and provides quick access to the full path.

**New Features:**
- Learning Path Progress section at top of lesson
- "View Full Path" button (🗺️) linking to learning_path.php
- Displays current progress in the path
- Responsive design

### 5. **Modified dashboard.php**
Added a new button to access the learning path.

**New Feature:**
- 🗺️ Learning Path button in top actions (green, prominent)
- Directly links to learning_path.php?path_id=1

## Database Tables

### learning_paths
Stores learning path definitions
```sql
CREATE TABLE learning_paths (
    path_id INT AUTO_INCREMENT PRIMARY KEY,
    path_name VARCHAR(255) NOT NULL UNIQUE,
    path_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### path_lessons
Maps lessons to paths in sequential order
```sql
CREATE TABLE path_lessons (
    path_lesson_id INT AUTO_INCREMENT PRIMARY KEY,
    path_id INT NOT NULL,
    lesson_id INT,
    course VARCHAR(100),
    lesson_slug VARCHAR(100),
    lesson_order INT NOT NULL,
    time_estimate INT DEFAULT 15,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (path_id) REFERENCES learning_paths(path_id) ON DELETE CASCADE,
    UNIQUE KEY unique_path_lesson (path_id, lesson_order)
);
```

### user_path_progress
Tracks user progress through learning paths
```sql
CREATE TABLE user_path_progress (
    progress_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    path_lesson_id INT NOT NULL,
    locked BOOLEAN DEFAULT TRUE,
    completed BOOLEAN DEFAULT FALSE,
    completion_percentage INT DEFAULT 0,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (path_lesson_id) REFERENCES path_lessons(path_lesson_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_path_lesson (user_id, path_lesson_id)
);
```

## Setup Instructions

### 1. Run Database Initialization
Access the initialization script to set up all tables and initial data:
```
http://localhost/codenest/init_learning_path.php
```

This will:
- Create all three new database tables
- Create the "Web Development Fundamentals" learning path
- Add 5 lessons to the path:
  1. HTML - intro (20 min)
  2. CSS - basics (25 min)
  3. JavaScript - intro (30 min)
  4. PHP - intro (35 min)
  5. JavaScript - basics (30 min)
- Initialize user progress for all users (first lesson unlocked, rest locked)

### 2. Access the Learning Path
- From Dashboard: Click the 🗺️ Learning Path button
- Direct URL: `learning_path.php?path_id=1`

## User Flow

1. **User views learning path** → Sees visual roadmap with current progress
2. **User clicks on unlocked lesson** → Goes to lesson.php with lesson content
3. **User completes lesson and takes quiz** → Quiz checks score
4. **If score > 70%** → Next lesson automatically unlocks
5. **User sees next lesson preview** → Can immediately start next lesson
6. **Repeat until all lessons completed** → See "🏆 Path Complete!" message

## Design Features

### Visual Elements
- **Progress Bar**: Shows overall completion percentage with green glow effect
- **Node Map**: Each lesson is a circular node with status icon
- **Connectors**: Lines connecting lessons showing path progression
- **Status Icons**:
  - 🔒 Locked (gray, 50% opacity)
  - ⭐ Current (glowing pulse effect)
  - ✅ Completed (bright glow)
- **Time Estimates**: Shows how long each lesson takes
- **Responsive**: Works on mobile, tablet, and desktop

### CSS Classes
- `.learning-path-page`: Main container
- `.roadmap`: Flex column of all lessons
- `.roadmap-item`: Individual lesson node (locked/current/completed)
- `.node`: Circular progress indicator
- `.lesson-card`: Lesson information card
- `.progress-bar-fill`: Green progress bar with gradient

## Customization

### Add a New Lesson to Path
```php
// Add a new lesson to the path (after initialization)
$stmt = $conn->prepare(
    "INSERT INTO path_lessons (path_id, course, lesson_slug, lesson_order, time_estimate) 
     VALUES (?, ?, ?, ?, ?)"
);
$stmt->bind_param("issii", $path_id, $course, $slug, $order, $time);
$stmt->execute();
```

### Create a New Learning Path
```php
// Create a new path
$stmt = $conn->prepare(
    "INSERT INTO learning_paths (path_name, path_description) VALUES (?, ?)"
);
$stmt->bind_param("ss", $name, $description);
$stmt->execute();
```

## Error Handling

- All API endpoints return JSON with success/error flags
- Quiz API calls use try/catch to gracefully handle failures
- API requires user authentication (session check)
- Missing prerequisites show locked state with explanation
- Database errors are logged but don't crash the page

## Security

- All user inputs are properly escaped/parameterized
- Session authentication required for API access
- User can only access their own progress data
- API validates user_id matches authenticated user
- SQL injection prevented with prepared statements

## Future Enhancements

- [ ] Multiple learning paths (Data Science, Mobile Dev, etc.)
- [ ] Achievements/badges when completing paths
- [ ] Estimated completion time remaining
- [ ] Skip ahead for advanced users (if they pass challenge)
- [ ] Path rating and reviews
- [ ] Leaderboard for fastest path completion
- [ ] Path completion certificates
- [ ] Video previews of lessons
- [ ] Difficulty levels (Beginner, Intermediate, Advanced)
