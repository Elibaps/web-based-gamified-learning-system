# CodeNest Agent Instructions

## Project Overview

CodeNest is a web-based 2D adventure game for teaching Java programming to STI Mobile App and Web Development (MAWD) students.

The application uses RPG-inspired gamification to make Java lessons and assessments more interactive.

## Important Platform Clarification

CodeNest is a WEB-BASED application only.

There is no mobile application component.

"MAWD" means Mobile App and Web Development and refers to the STI student group targeted by the project. It does NOT mean that CodeNest should have a mobile application.

Do not introduce Android, Kotlin, React Native, Flutter, or other mobile application development unless explicitly requested.

Java is the programming subject being taught inside CodeNest.

The CodeNest web application itself uses PHP, MySQL, HTML, CSS, and JavaScript.

## IMPORTANT: Current Project Scope

CodeNest now focuses ONLY on Java as its programming-learning subject.

Older versions of the project attempted to support multiple programming languages such as:

- HTML
- CSS
- JavaScript
- PHP
- C++
- Java

This multi-language direction is obsolete.

Do not expand or restore the old multi-language learning system unless explicitly requested.

Existing code related to other programming-language courses may be legacy code and should be reviewed before being reused.

The current educational direction is:

Java Learning Path
→ Java Lessons
→ Java Assessments
→ RPG Challenges
→ Progression / XP
→ Advanced Java Topics

## Technology Stack

The CodeNest WEB APPLICATION uses:

- PHP
- MySQL / MariaDB
- HTML
- CSS
- JavaScript
- XAMPP

Java is the programming language TAUGHT by CodeNest.

Do not rewrite the web application itself in Java unless explicitly requested.

## Development Rules

1. Work only inside the CodeNest repository.
2. Do not access unrelated files outside the project.
3. Do not modify unrelated files.
4. Do not delete files unless explicitly requested.
5. Preserve existing working functionality.
6. Prefer focused changes over large refactors.
7. Do not introduce frameworks or major dependencies without approval.
8. Inspect existing code before modifying it.
9. Do not assume legacy multi-language features are still required.
10. Ask before making major architectural changes.

## Java Learning Direction

New educational features should support the Java-focused learning path.

Lessons and assessments should progress from beginner concepts toward more advanced concepts in a structured order.

Avoid introducing unrelated programming languages into the learning path.

## UI Direction

Preserve CodeNest's existing:

- Pixel-art / RPG visual identity
- Adventure-game presentation
- Light and dark theme support
- Existing animations and game elements
- Responsive behavior

Do not replace the interface with a generic dashboard design.

## Git Safety

Do not automatically:

- commit
- push
- reset
- force push
- delete branches
- rewrite Git history

unless explicitly requested.

After changes, the developer should be able to inspect them with:

git status
git diff

## Database Safety

Do not drop tables, delete user data, reset the database, or make destructive schema changes unless explicitly requested.

Explain significant database changes before implementing them.

## Legacy Code

The repository may contain code created for the previous multi-language version of CodeNest.

When encountering legacy functionality:

1. Determine whether it is still relevant to the Java-focused scope.
2. Do not automatically delete it.
3. Report obsolete or conflicting functionality.
4. Recommend whether it should be kept, adapted, or removed.
5. Wait for approval before significant removal.

## After Making Changes

Report:

- What was changed
- Which files were changed
- Why they were changed
- What should be manually tested