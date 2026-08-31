CREATE DATABASE IF NOT EXISTS buyclose;

USE buyclose;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'vendor', 'rider') NOT NULL DEFAULT 'customer',
    status ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS orders ( id INT AUTO_INCREMENT PRIMARY KEY,
customer_id INT NOT NULL,
vendor_id INT NOT NULL,

status ENUM(
    'pending',
    'confirmed',
    'preparing',
    'ready_for_pickup',
    'assigned',
    'picked_up',
    'delivered',
    'cancelled'
) NOT NULL DEFAULT 'pending',

total_amount DECIMAL(10,2) NOT NULL,

delivery_address TEXT NOT NULL,

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

FOREIGN KEY (customer_id)
    REFERENCES users(id)
    ON DELETE CASCADE,

FOREIGN KEY (vendor_id)
    REFERENCES users(id)
    ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS riders ( id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NOT NULL UNIQUE,

verification_status ENUM(
    'pending',
    'verified',
    'rejected'
) NOT NULL DEFAULT 'pending',

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS deliveries ( id INT AUTO_INCREMENT PRIMARY KEY,
order_id INT NOT NULL UNIQUE,

rider_id INT NOT NULL,

status ENUM(
    'assigned',
    'accepted',
    'picked_up',
    'delivered',
    'cancelled'
) NOT NULL DEFAULT 'assigned',

accepted_at TIMESTAMP NULL,

picked_up_at TIMESTAMP NULL,

delivered_at TIMESTAMP NULL,

delivery_proof TEXT NULL,

created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

FOREIGN KEY (order_id)
    REFERENCES orders(id)
    ON DELETE CASCADE,

FOREIGN KEY (rider_id)
    REFERENCES riders(id)
    ON DELETE CASCADE
);
