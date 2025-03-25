# Plane Club

Plane Club is a web application for plane spotters and aviation enthusiasts. Users can upload their own sightings and photos to build a personalised gallery.

---

## Tech Stack

- **PHP 8.3** – For modern, secure, and performant server-side code.  
- **Laravel 11** – Provides a robust MVC framework, clean architecture, and an expressive syntax.  
- **Livewire 3 (Volt class-based methodology)** – Enables reactive components and dynamic UIs without complicated front-end frameworks.  
- **PostgreSQL** – A reliable and scalable relational database.

---

## Using Plane Club in Production
* Visit the Production URL: [https://planeclub.example.com](https://planeclub.app/)
* Sign Up: Click on Signup and complete the form with your email, password, and any required details.
* Confirm Email: Check your inbox and verify your account.
* Explore: Start uploading photos of your plane sightings, tagging them, and managing your gallery.

### Creating Your First Post
* Create New Post from the main menu.
* Upload Photo and add relevant details like aircraft model, location, or date.


---

## Getting Started (Local Setup)

### Prerequisites

1. **PHP 8.3**  
2. **Composer**  
3. **Node.js & npm** (for front-end dependencies)  
4. **PostgreSQL** (version 13+ recommended)

### Installation Steps

1. **Clone the Repository**  
   ```bash
   git clone https://github.com/[YourUsername]/plane-club.git
   cd plane-club
   ```

2. Install Composer Dependencies

    ```bash
    composer install
    ```

3. Install Front-End Dependencies

```bash
npm install
npm run build  # or: npm run dev
```

4. Create and Configure Your .env File

* Duplicate the .env.example file and rename it to .env.
*Update the environment variables (e.g., DB_DATABASE, DB_USERNAME, DB_PASSWORD).

5. Run Database Migrations

    ```bash
    php artisan migrate
    ```
    
6. Serve the Application

    ```bash
    php artisan serve
    ```

Visit http://127.0.0.1:8000 to access the application locally.
