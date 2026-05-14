# User Account System Documentation

This document describes the complete user authentication and management system implemented in the project.

## Features

### 1. User Registration
- Users can create new accounts with name, email, and password
- Password confirmation required
- Terms and conditions acceptance required
- Automatic redirection to home page after successful registration

### 2. User Login
- Users can log in with email and password
- Secure token-based authentication
- Automatic redirection to home page after successful login
- Session persistence with localStorage

### 3. Admin User Management
- Admin-only page to view all registered users
- Search functionality to find users by name or email
- Promote regular users to admin status
- Demote admin users back to regular users
- Delete user accounts
- Real-time role and status updates

## Backend Components

### Database
**Migration File**: `backend/database/migrations/0001_01_01_000000_create_users_table.php`

Users table structure:
- `id`: Primary key
- `name`: User's full name
- `email`: Unique email address
- `password`: Hashed password
- `is_admin`: Boolean flag for admin status (default: false)
- `created_at`, `updated_at`: Timestamps

### Models
**File**: `backend/app/Models/User.php`

The User model includes:
- `fillable` attributes: name, email, password, is_admin
- Password hashing via `'password' => 'hashed'` cast
- Support for admin identification

### Controllers

#### AuthController
**File**: `backend/app/Http/Controllers/Api/AuthController.php`

Endpoints:
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login user
- `POST /api/auth/logout` - Logout user
- `GET /api/auth/me` - Get current user info

#### UserController  
**File**: `backend/app/Http/Controllers/Api/UserController.php`

Admin-only endpoints:
- `GET /api/admin/users` - List all users
- `GET /api/admin/users/{id}` - Get user details
- `PUT /api/admin/users/{id}` - Update user
- `DELETE /api/admin/users/{id}` - Delete user
- `PUT /api/admin/users/{id}/promote` - Promote to admin
- `PUT /api/admin/users/{id}/demote` - Demote from admin

### Middleware
**File**: `backend/app/Http/Middleware/CheckAdmin.php`

Validates:
- User has valid authentication token
- User has admin status (is_admin = true)
- Returns 401 for invalid/missing tokens
- Returns 403 for non-admin users

### Routes
**File**: `backend/routes/api.php`

Public routes:
```
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/logout
GET    /api/auth/me
```

Admin routes (protected by `check.admin` middleware):
```
GET    /api/admin/users
GET    /api/admin/users/{id}
PUT    /api/admin/users/{id}
DELETE /api/admin/users/{id}
PUT    /api/admin/users/{id}/promote
PUT    /api/admin/users/{id}/demote
```

## Frontend Components

### Pinia Store
**File**: `frontend/src/stores/auth.js`

State management includes:
- `user`: Current logged-in user object
- `token`: Authentication token
- `loading`: Loading state indicator
- `error`: Error messages
- `isAuthenticated`: Computed property for auth status
- `isAdmin`: Computed property for admin status

Methods:
- `register(name, email, password, passwordConfirmation)` - Register new user
- `login(email, password)` - Login user
- `logout()` - Clear auth state
- `fetchCurrentUser()` - Get current user from API
- `initializeAuth()` - Initialize auth from localStorage on app start

### Pages/Views

#### Login Page
**File**: `frontend/src/views/Login.vue`

Features:
- Email and password input fields
- Error message display
- Link to registration page
- Form validation
- Loading state during submission

#### Register Page
**File**: `frontend/src/views/Register.vue`

Features:
- Full name, email, password input fields
- Password confirmation field
- Terms and conditions checkbox
- Password match validation
- Form validation and error handling
- Link to login page

#### Admin Users Management
**File**: `frontend/src/views/AdminUsers.vue`

Features:
- Display all registered users in a table
- Search users by name or email
- Promote users to admin
- Demote admins to regular users
- Delete user accounts
- Real-time updates after actions
- Responsive table design

### Router
**File**: `frontend/src/router/index.js`

Routes:
- `/` - Home page
- `/items` - Items listing
- `/register` - Registration (guest only)
- `/login` - Login (guest only)
- `/admin/users` - Admin user management (admin only)

Route guards:
- Redirect authenticated users away from login/register
- Redirect unauthenticated users to login for admin pages
- Redirect non-admin users away from admin pages

### Navigation Component
**File**: `frontend/src/App.vue`

Updated header with:
- Conditional navigation links based on auth status
- User name display when logged in
- Dropdown menu with logout option
- Admin menu link (visible to admin users only)
- Mobile-responsive design
- Smooth dropdown animations

### API Service
**File**: `frontend/src/services/api.js`

- Configured axios instance with Laravel API base URL
- Request/response interceptors
- 401 error handling (redirects to login)
- Token header management

## Setup Instructions

### Backend Setup

1. **Run migrations to create the users table:**
   ```bash
   cd backend
   php artisan migrate
   ```

2. **Start Laravel development server:**
   ```bash
   php artisan serve
   ```

The API will be available at `http://localhost:8000/api`

### Frontend Setup

1. **Install dependencies:**
   ```bash
   cd frontend
   npm install
   ```

2. **Start development server:**
   ```bash
   npm run dev
   ```

The frontend will be available at `http://localhost:5173`

## Usage Guide

### Authentication Flow

1. **Register New Account**
   - Navigate to `/register`
   - Fill in name, email, password, and confirm password
   - Accept terms and conditions
   - Click "Create an account"
   - Automatically logged in and redirected to home page

2. **Login**
   - Navigate to `/login`
   - Enter email and password
   - Click "Login"
   - Redirected to home page upon success

3. **Logout**
   - Click user dropdown in navbar (top right)
   - Select "Logout"
   - Redirected to login page

### Admin Features

1. **Access Admin Panel**
   - Must be logged in as admin user
   - Navigate to `/admin/users` or click "User Management" in navbar
   - View all registered users

2. **Manage Users**
   - **Search**: Use search bar to find users by name or email
   - **Promote**: Click "Promote" to make user an admin
   - **Demote**: Click "Demote" to remove admin status
   - **Delete**: Click "Delete" to remove user from system

## Creating Admin Users

To create the first admin user, run a Laravel Tinker command:

```bash
php artisan tinker
User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => Hash::make('password123'),
    'is_admin' => true
])
```

## Security Considerations

1. **Passwords**: Stored as hashed values using Laravel's default bcrypt
2. **Token Authentication**: Simple base64 token encoding (for demo purposes)
3. **Admin Middleware**: All admin routes protected by CheckAdmin middleware
4. **Route Guards**: Vue Router enforces role-based access control

**Note**: For production, consider implementing:
- JWT tokens (using Laravel Sanctum or Passport)
- HTTPS enforcement
- CSRF protection
- Rate limiting
- Comprehensive input validation

## API Request Examples

### Register
```bash
POST /api/auth/register
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

### Login
```bash
POST /api/auth/login
{
    "email": "john@example.com",
    "password": "password123"
}
```

### Get Admin Users (requires Bearer token)
```bash
GET /api/admin/users
Headers: Authorization: Bearer <token>
```

### Promote User to Admin
```bash
PUT /api/admin/users/1/promote
Headers: Authorization: Bearer <token>
```

## Troubleshooting

### Login Issues
- Verify email and password are correct
- Ensure Laravel backend is running
- Check browser console for error messages
- Clear localStorage if session issues persist

### Admin Panel Not Accessible
- Ensure you're logged in (check navbar for user menu)
- Verify your account is marked as admin in database
- Try logging out and back in

### API Connection Errors
- Check that Laravel server is running on `http://localhost:8000`
- Verify CORS is enabled in Laravel
- Check network tab in browser DevTools for request details

## Future Enhancements

- Password reset functionality
- Email verification
- Two-factor authentication
- User profile pages
- Activity logging
- Advanced search and filtering
- Pagination for user lists
- Bulk user management actions
