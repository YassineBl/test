# OML PARA - Dynamic Parapharmacy Platform

A complete PHP-based e-commerce system for managing a parapharmacy business with product catalog, admin interface, and WhatsApp order integration.

## ✨ Features

### 🛍️ Customer Features
- **Product Catalog**: Browse products by category
- **Easy Ordering**: Simple order form with WhatsApp confirmation
- **Responsive Design**: Works on desktop and mobile
- **Category Filtering**: Sort products by category
- **Real-time Stock**: See available inventory
- **Product Details**: View descriptions, prices, and availability

### 👨‍💼 Admin Panel Features

#### Dashboard
- 📊 Real-time statistics (products, categories, orders, revenue)
- 🎯 Quick action buttons for common tasks
- 💰 Revenue tracking

#### Product Management
- ➕ Add/Edit/Delete products
- 📸 Image URL support
- 📦 Stock quantity tracking
- 🏷️ Category assignment
- 🚫 Enable/Disable products
- 💵 Price management

#### Category Management
- ➕ Create/Edit/Delete categories
- 📝 Category descriptions
- 🔗 Automatic slug generation
- 📊 View product count per category

#### Order Management
- 📥 View all customer orders
- 📋 Order details and history
- 🔄 Update order status (Pending → Confirmed → Shipped → Delivered)
- 💬 Send WhatsApp notifications
- 👤 Customer contact information
- 📞 Order tracking

### 🔄 WhatsApp Integration
- **Automatic Confirmation**: Customers get instant order confirmation via WhatsApp
- **Order Updates**: Send status updates via WhatsApp
- **Admin Notifications**: Send messages directly from admin panel
- **No Third-party API**: Uses direct WhatsApp share links

## 📁 Project Structure

```
/test
├── index.php                 # Homepage
├── products.php              # Product catalog
├── products.js               # Product filtering
├── config.php                # Database & app config
├── database_schema.sql       # Database structure
├── SETUP_GUIDE.md           # Setup instructions
├── README.md                # This file
│
├── admin/                   # Admin panel
│   ├── login.php           # Admin login
│   ├── dashboard.php       # Dashboard
│   ├── products.php        # Product management
│   ├── categories.php      # Category management
│   ├── orders.php          # Order management
│   └── includes/
│       ├── auth.php        # Authentication functions
│       └── utils.php       # Utility functions
│
├── api/                    # API endpoints
│   └── place-order.php    # Order placement API
│
├── uploads/                # Product images (writable)
│
└── styles.css             # Main stylesheet
```

## 🚀 Quick Start

### 1. Prerequisites
- PHP 7.4+
- MySQL 5.7+
- Web server (Apache/Nginx)

### 2. Installation
1. Import `database_schema.sql` into MySQL
2. Update credentials in `config.php`
3. Set proper file permissions for `/uploads`

### 3. Default Credentials
- **Username**: `admin`
- **Password**: `admin123`

⚠️ Change immediately after first login!

### 4. Access URLs
- **Frontend**: `http://localhost/test/`
- **Admin**: `http://localhost/test/admin/login.php`

## 🔐 Security Features

- ✅ Password hashing (bcrypt)
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS protection (sanitization)
- ✅ Session management
- ✅ Admin authentication
- ✅ Input validation

## 📱 API Endpoints

### Place Order
**POST** `/api/place-order.php`

```json
{
  "customer_name": "John Doe",
  "customer_phone": "+1234567890",
  "customer_email": "john@example.com",
  "product_id": 1,
  "quantity": 2,
  "product_price": 29.99,
  "notes": "Optional notes"
}
```

## 💾 Database Schema

### Orders Table
- Stores all customer orders
- Tracks order status (pending, confirmed, shipped, delivered, cancelled)
- WhatsApp notification tracking
- Customer contact information

### Products Table
- Product name, description, price
- Stock quantity management
- Availability status
- Category assignment
- Image URL support

### Categories Table
- Category names and descriptions
- URL-friendly slugs
- Product count tracking

### Admin Users Table
- Secure password storage
- Email verification
- Multiple admin support

## 🎨 Customization

### Update Company Info
Edit `config.php`:
```php
define('WHATSAPP_PHONE', 'your_number');
```

### Update Styles
Modify `styles.css` for custom colors and fonts

### Add Categories
Use admin panel to create new product categories

## 📊 Reports & Analytics

The system tracks:
- Total revenue
- Pending orders
- Product inventory
- Customer orders
- Order status breakdown

## 🆘 Troubleshooting

### Database Connection Fails
1. Verify MySQL is running
2. Check credentials in `config.php`
3. Ensure database exists

### Admin Login Fails
1. Clear browser cache/cookies
2. Verify admin user exists in database
3. Check password is correct

### Orders Not Appearing
1. Verify database connection
2. Check order API response
3. Review browser console for errors

## 📧 Support & Contact

For support or questions:
- Email: support@omlpara.com
- WhatsApp: +1234567890

## 📄 License

All rights reserved © 2026 OML PARA

## 🔄 Version History

**v1.0.0** (Feb 15, 2026)
- Initial release
- Product management
- Order management
- WhatsApp integration
- Admin dashboard
- Customer portal

---

**Last Updated**: February 15, 2026
