# HandScribe Starter (PHP + Bootstrap)

## Purpose and Features

Handscribe is a web application designed to break down communication barriers between deaf and hearing individuals in a streamlined and intuitive fashion. It utilizes real time footage through cameras in user devices and recordings of ASL signing and translates it into English. The translation goes the other way as well, where audio or text can be input to visualize a live sign language translation on a virtual avatar. The key features include the following;

- All-in-one model, no extra devices or software needed
- Real time translation from:
    Audio -> ASL Avatar | Text -> ASL Avatar | Video ASL -> Subtitles & Live Audio
- Can use both live and pre-recorded videos


This project is a proof of concept for a university capstone course.

## Current Features

### ✅ Implemented Features
- **Live Camera Interface** — Real-time camera feed with permission handling
- **Mobile-First Design** — Responsive interface optimized for mobile devices
- **User Authentication** — Login system with username/password
- **Translation History** — Track and view previous translation sessions
- **Progress Tracking** — Visual progress indicators during translation
- **Error Handling** — Comprehensive camera access error management
- **Modern UI** — Clean, professional interface with custom styling

### 🚧 In Development
- **ASL Recognition** — Video-to-text translation engine
- **Avatar System** — Text-to-ASL avatar visualization
- **Educational Resources** — Learning materials and tutorials
- **Audio Processing** — Speech-to-text capabilities

## Quickstart

### Requirements
- **PHP 8+** — Server-side processing
- **MySQL Database** — User data and translation history storage
- **Modern Web Browser** — Camera API support (Chrome, Firefox, Safari)
- **HTTPS Connection** — Required for camera access in production

### Database Setup
1. Create MySQL database: `infost490fa2505_handscribe`
2. Update connection details in `includes/mysqli_connect.php`
3. Import any required database schemas

### Run locally (Windows PowerShell)

```powershell
# From the project root
cd public
php -S localhost:8000
```

Open `http://localhost:8000` in your browser.

## Application Pages

### Main Translation Interface (`/`)
- Live camera feed for ASL recognition
- Real-time translation display
- Progress tracking and session controls
- Language selection options

### User Dashboard (`/user.php`)
- User authentication and login
- Translation history with session details
- Account management features

### Educational Resources (`/education.php`)
- Learning materials and tutorials (coming soon)
- ASL practice exercises

### Avatar Customization (`/avatar.php`)
- Avatar appearance settings (coming soon)
- Sign language avatar configuration

## Structure

- `public/` — Web root, main application pages
  - `index.php` — Main translation interface with camera feed
  - `user.php` — User login and translation history
  - `education.php` — Educational resources (coming soon)
  - `avatar.php` — Avatar customization (coming soon)
- `includes/` — Shared PHP components
  - `header.php` — Mobile-first HTML head and navigation
  - `footer.php` — Footer and closing HTML
  - `mysqli_connect.php` — Database connection configuration
- `assets/` — Static resources
  - `css/styles.css` — Mobile-first responsive styles
  - `js/main.js` — Camera API and user interaction logic
  - `images/` — Logo, avatars, and UI graphics

## Technical Implementation

### Frontend Technologies
- **Bootstrap 5.3.3** — Responsive UI framework
- **Vanilla JavaScript** — Camera API integration and user interactions
- **CSS3** — Mobile-first responsive design with CSS custom properties
- **MediaDevices API** — Camera access and video stream handling

### Backend Technologies
- **PHP 8+** — Server-side processing and page rendering
- **MySQL** — Database for user data and translation history
- **mysqli** — Database connection and query handling

### Key Features Implementation
- **Camera Integration** — Uses `navigator.mediaDevices.getUserMedia()` for live video
- **Error Handling** — Comprehensive camera permission and device error management
- **Mobile Optimization** — Responsive design with mobile-first approach
- **Session Management** — User authentication and translation history tracking

## Customize

- Edit `includes/header.php` to change the navbar, title, or add meta tags.
- Edit `assets/css/styles.css` for custom branding.
- Add new pages under `public/` and include the header/footer for consistency.
