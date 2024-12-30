# Fresh Binge - Online Fruits & Vegetables E-commerce API

Fresh Binge is a modern e-commerce API built with Laravel, designed to power an online platform for selling fresh fruits and vegetables across various locations in India. The API provides a robust backend system for managing products, categories, shopping carts, and pincode-based delivery services.

## Service Areas

Fresh Binge operates through a pincode-based delivery system across India. Each service location includes:
- 6-digit pincode
- Area name
- District
- State
- Active/Inactive status

Example Service Areas:
```
Kerala:
- 682001 (Ernakulam South)
- 682016 (Edappally)
- 682024 (Kakkanad)

Tamil Nadu:
- 600001 (Parrys)
- 600002 (Egmore)
- 600006 (Mylapore)

And more...
```

Check if your area is serviceable using the `/check-serviceability` endpoint with your pincode.

## Features

### Product Management
- **Categories & Subcategories**: Organize products into hierarchical categories (e.g., Fruits, Vegetables, Organic Products)
- **Product Variants**: Support for different variants of products (e.g., packaging sizes, bulk quantities)
- **Stock Management**: Real-time inventory tracking
- **Image Support**: Multiple image uploads for products and categories

### Shopping Experience
- **Smart Cart System**: 
  - Support for both guest and authenticated users
  - Automatic cart merging when guest users log in
  - Real-time price calculations
  - Multiple quantity management

### Location Management
- **Pincode-Based Service Areas**:
  - Quick serviceability check by pincode
  - District and state-wise organization
  - Simple active/inactive status management

### User Management
- **Authentication**: Secure user registration and login using Laravel Sanctum
- **Address Management**: Multiple delivery address support with default address setting
- **Order History**: Track and manage user orders

### Admin Features
- **Inventory Management**: Track and update product stock levels
- **Category Management**: Create and manage product categories
- **Order Processing**: Process and manage customer orders
- **User Management**: Manage customer accounts and permissions
- **Service Area Management**: 
  - Add/remove serviceable pincodes
  - Update area details
  - Enable/disable delivery to specific pincodes

## Authentication

The application uses a secure multi-factor authentication system with OTP verification:

### Registration
1. Initial registration with name, mobile, and password
2. OTP verification:
   - Mobile SMS OTP verification
   - WhatsApp OTP verification (if mobile number is registered on WhatsApp)

### Login
Two methods available:

1. **Password-based Login**:
   ```json
   POST /api/login
   {
       "mobile": "9876543210",
       "login_type": "password",
       "password": "your_password"
   }
   ```

2. **OTP-based Login**:
   - First, request OTP:
     ```json
     POST /api/login/request-otp
     {
         "mobile": "9876543210"
     }
     ```
   - Then, login with OTP:
     ```json
     POST /api/login
     {
         "mobile": "9876543210",
         "login_type": "otp",
         "mobile_otp": "123456",
         "whatsapp_otp": "123456",
         "whatsapp_enabled": true
     }
     ```

### Email Management
- Email is optional and can be added/updated in the profile section
- Email verification is required when adding or updating email
- Email OTP verification is used for email verification

### OTP Features
- 6-digit numeric OTPs
- 10-minute validity period
- Resend functionality available
- Separate verification tracking for mobile and email

### API Endpoints

#### Authentication
```
POST /api/register           - Step 1: Register with credentials
POST /api/register/verify   - Step 2: Verify registration OTPs
POST /api/login             - Step 1: Login with credentials
POST /api/login/verify      - Step 2: Verify login OTPs
POST /api/resend-otp        - Resend OTP for mobile/whatsapp
POST /api/logout            - Logout (requires authentication)
```

## Technical Stack

- **Framework**: Laravel 10.x
- **Authentication**: Laravel Sanctum
- **Database**: MySQL
- **Image Storage**: Laravel Storage with public disk

## Installation

1. Clone the repository:
```bash
git clone https://github.com/eduzereindia/freshbinge_api.git
```

2. Install dependencies:
```bash
composer install
```

3. Create environment file:
```bash
cp .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Configure your database in `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

6. Run migrations:
```bash
php artisan migrate
```

7. Create storage link for images:
```bash
php artisan storage:link
```

## API Documentation

The API documentation is available in the Postman collection included in the repository. Import the `freshb-ecommerce.postman_collection.json` file into Postman to explore all available endpoints.

### Key Endpoints

- **Authentication**
  - `POST /api/register`: Register new user
  - `POST /api/login`: User login
  - `POST /api/logout`: User logout

- **Categories**
  - `GET /api/categories`: List all categories
  - `GET /api/categories/{category}`: Get category details
  - `GET /api/categories/{category}/subcategories`: Get subcategories

- **Products**
  - `GET /api/products`: List all products
  - `GET /api/products/{product}`: Get product details
  - `GET /api/products/{product}/variants`: Get product variants

- **Cart**
  - `GET /api/cart`: View cart
  - `POST /api/cart/add`: Add item to cart
  - `PUT /api/cart/items/{cartItem}`: Update cart item
  - `DELETE /api/cart/items/{cartItem}`: Remove item from cart

- **Service Areas**
  - `GET /api/service-locations`: List all serviceable pincodes
  - `POST /api/check-serviceability`: Check if pincode is serviceable
  - `POST /api/service-locations`: Add new serviceable pincode (Admin)
  - `PUT /api/service-locations/{id}`: Update pincode details (Admin)
  - `DELETE /api/service-locations/{id}`: Remove serviceable pincode (Admin)

## Security

The API implements several security measures:
- Token-based authentication using Laravel Sanctum
- Protected routes for authenticated users
- Admin middleware for administrative actions
- CORS protection
- Input validation and sanitization

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## About

Fresh Binge is committed to delivering fresh, high-quality fruits and vegetables to customers through a convenient online platform. Our pincode-based delivery system ensures efficient service across multiple locations in India, with real-time serviceability checks and location-specific delivery options.
