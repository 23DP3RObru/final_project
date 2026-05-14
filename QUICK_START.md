# Quick Start Guide: User Account System

## What Was Built

A complete user authentication system with the following features:
- ✅ User registration with email and password
- ✅ User login with secure authentication
- ✅ Admin-only user management dashboard
- ✅ Promote/demote users to admin status
- ✅ Delete user accounts
- ✅ Search users by name or email
- ✅ Role-based access control
- ✅ Responsive design for mobile and desktop

## File Structure

### Backend Files Created/Modified

```
backend/
├── database/
│   └── migrations/
│       └── 0001_01_01_000000_create_users_table.php (NEW)
├── app/
│   ├── Models/
│   │   └── User.php (MODIFIED - added is_admin field)
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AuthController.php (NEW)
│   │   │   └── UserController.php (NEW)
│   │   └── Middleware/
│   │       └── CheckAdmin.php (NEW)
└── routes/
    └── api.php (MODIFIED - added auth routes)
bootstrap/
└── app.php (MODIFIED - registered middleware)
```

### Frontend Files Created/Modified

```
frontend/
├── src/
│   ├── stores/
│   │   └── auth.js (NEW - Pinia store)
│   ├── views/
│   │   ├── Login.vue (MODIFIED)
│   │   ├── Register.vue (MODIFIED)
│   │   └── AdminUsers.vue (NEW)
│   ├── router/
│   │   └── index.js (MODIFIED - added routes & guards)
│   ├── services/
│   │   └── api.js (MODIFIED)
│   ├── App.vue (MODIFIED - user menu & navbar)
│   └── main.js (MODIFIED - auth initialization)
```

## Getting Started

### Step 1: Run Database Migrations

```bash
cd backend
php artisan migrate
```

This creates the `users` table with auth fields.

### Step 2: Create First Admin User

```bash
php artisan tinker
```

Then paste this code:
```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => Hash::make('password123'),
    'is_admin' => true
]);
exit
```

### Step 3: Start Laravel Server

```bash
php artisan serve
```

Laravel will run on `http://localhost:8000`

### Step 4: Start Vue.js Frontend (in new terminal)

```bash
cd frontend
npm install  # Only needed first time
npm run dev
```

Frontend will run on `http://localhost:5173`

## Testing the System

### Test Registration
1. Go to `http://localhost:5173/register`
2. Fill in the form:
   - Name: "Test User"
   - Email: "test@example.com"
   - Password: "password123"
   - Confirm Password: "password123"
3. Accept terms and click "Create an account"
4. You should be logged in and redirected to home page

### Test Login
1. Go to `http://localhost:5173/login`
2. Enter:
   - Email: "admin@example.com"
   - Password: "password123"
3. Click "Login"
4. You should see your name in top right corner

### Test Admin Panel
1. After logging in as admin, click "User Management" in navbar
2. You should see a table of all users
3. Try the following:
   - Search for users
   - Promote/demote a user
   - Delete a user (will ask for confirmation)

### Test Logout
1. Click your name/avatar in top right
2. Click "Logout"
3. You should be logged out and redirected to login page

## API Endpoints

All endpoints are available at `http://localhost:8000/api`

### Public Endpoints
```
POST   /auth/register          - Register new user
POST   /auth/login             - Login user
POST   /auth/logout            - Logout user
GET    /auth/me                - Get current user (requires token)
```

### Admin-Only Endpoints (require Bearer token)
```
GET    /admin/users            - List all users
GET    /admin/users/{id}       - Get user details
PUT    /admin/users/{id}       - Update user
DELETE /admin/users/{id}       - Delete user
PUT    /admin/users/{id}/promote   - Make user admin
PUT    /admin/users/{id}/demote    - Remove admin status
```

## Testing with Postman/cURL

### Register Example
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Login Example
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password123"
  }'
```

This returns a token. Copy it for use in next requests.

### Get Users (as Admin)
```bash
curl -X GET http://localhost:8000/api/admin/users \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## Key Features Explained

### Authentication
- Uses **token-based authentication**
- Token stored in **localStorage** for persistence
- Automatically added to all API requests
- User session persists on page refresh

### Authorization
- **CheckAdmin middleware** protects admin routes
- Vue Router **guards** prevent unauthorized page access
- Non-admin users redirected away from admin pages

### User Management
- Full CRUD operations on users
- Promote/demote admin status
- Real-time updates after actions
- Search functionality

### User Experience
- Responsive mobile design
- Smooth transitions and animations
- Error messages for invalid inputs
- Loading states during API calls
- Dropdown menu for user actions

## Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| "Cannot GET /api/auth/login" | Ensure Laravel is running on port 8000 |
| Login fails with no error | Check email/password, verify user exists in DB |
| Admin page showing 403 error | User must have `is_admin = true` in database |
| Token expired/invalid | Clear localStorage and log in again |
| CORS errors | Verify Laravel CORS config allows frontend origin |

## Next Steps (Optional Enhancements)

- [ ] Add password reset functionality
- [ ] Implement email verification
- [ ] Add two-factor authentication
- [ ] Create user profile editing page
- [ ] Add activity logging
- [ ] Implement pagination for user lists
- [ ] Add user roles/permissions system
- [ ] Create audit trail for admin actions

## Documentation

For detailed documentation, see [AUTHENTICATION_SYSTEM.md](./AUTHENTICATION_SYSTEM.md)

Contains:
- Complete component descriptions
- All API endpoints
- Configuration details
- Security considerations
- Troubleshooting guide
