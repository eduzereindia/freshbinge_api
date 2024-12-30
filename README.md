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

# FreshB E-commerce API

A modern e-commerce API built with Laravel, focusing on fresh grocery delivery with features like service area management, OTP-based authentication, and real-time order tracking.

## Features

### Authentication
- Mobile-based registration and login
- Dual authentication methods:
  - Password-based login
  - OTP-based login (SMS + WhatsApp)
- Optional email verification
- Token-based authentication using Laravel Sanctum

### User Management
- Profile management
- Multiple address support
- Default address setting
- Order history
- Cart management

### Product Management
- Category and subcategory organization
- Product variants
- Stock management
- Price management
- Image handling

### Order Management
- Cart to order conversion
- Multiple payment methods
- Order tracking
- Delivery slot selection
- Service area validation

### Location Management
- Service area definition
- Pincode-based serviceability check
- Delivery slot management
- Dynamic pricing based on location

## API Documentation

### Authentication

#### Registration
```http
POST /api/auth/register
Content-Type: application/json

{
    "name": "John Doe",
    "mobile": "9876543210",
    "password": "password123",
    "email": "john@example.com" // Optional
}
```

#### Registration Verification
```http
POST /api/auth/register/verify
Content-Type: application/json

{
    "mobile": "9876543210",
    "mobile_otp": "123456",
    "whatsapp_otp": "123456",    // Required if WhatsApp enabled
    "whatsapp_enabled": true
}
```

#### Login
Two methods available:

1. Password-based Login:
```http
POST /api/auth/login
Content-Type: application/json

{
    "mobile": "9876543210",
    "login_type": "password",
    "password": "password123"
}
```

2. OTP-based Login:
```http
POST /api/auth/login/request-otp
Content-Type: application/json

{
    "mobile": "9876543210"
}
```

Then:
```http
POST /api/auth/login
Content-Type: application/json

{
    "mobile": "9876543210",
    "login_type": "otp",
    "mobile_otp": "123456",
    "whatsapp_otp": "123456",    // Required if WhatsApp enabled
    "whatsapp_enabled": true
}
```

### User Profile

#### Get Profile
```http
GET /api/user/profile
Authorization: Bearer {token}
```

#### Update Profile
```http
PUT /api/user/profile
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "mobile": "9876543210",
    "profile_photo": "file"      // Optional
}
```

#### Change Password
```http
POST /api/user/change-password
Authorization: Bearer {token}
Content-Type: application/json

{
    "current_password": "old_password",
    "new_password": "new_password",
    "new_password_confirmation": "new_password"
}
```

### Address Management

#### List Addresses
```http
GET /api/addresses
Authorization: Bearer {token}
```

#### Add Address
```http
POST /api/addresses
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "John Doe",
    "mobile": "9876543210",
    "address_line1": "123 Main St",
    "address_line2": "Apt 4B",       // Optional
    "landmark": "Near Park",         // Optional
    "city": "Mumbai",
    "state": "Maharashtra",
    "pincode": "400001",
    "address_type": "home",          // home, office, other
    "is_default": true,              // Optional
    "service_location_id": 1
}
```

#### Update Address
```http
PUT /api/addresses/{address_id}
Authorization: Bearer {token}
Content-Type: application/json

{
    // Same fields as Add Address
}
```

#### Delete Address
```http
DELETE /api/addresses/{address_id}
Authorization: Bearer {token}
```

#### Set Default Address
```http
POST /api/addresses/{address_id}/set-default
Authorization: Bearer {token}
```

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/freshb.git
cd freshb
```

2. Install dependencies:
```bash
composer install
```

3. Copy environment file:
```bash
cp .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Configure your database in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=freshb
DB_USERNAME=root
DB_PASSWORD=
```

6. Run migrations:
```bash
php artisan migrate
```

7. Start the server:
```bash
php artisan serve
```

## Environment Variables

```env
# Application
APP_NAME=FreshB
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=freshb
DB_USERNAME=root
DB_PASSWORD=

# SMS Gateway (example)
SMS_PROVIDER_API_KEY=
SMS_PROVIDER_SENDER_ID=

# WhatsApp Gateway (example)
WHATSAPP_API_KEY=
WHATSAPP_SENDER_NUMBER=

# File Storage
FILESYSTEM_DISK=public

# JWT
JWT_SECRET=
JWT_TTL=60
```

## Testing

Run the test suite:
```bash
php artisan test
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support, email support@freshb.com or join our Slack channel.
