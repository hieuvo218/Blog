-- Create the database
CREATE DATABASE blogsite CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use it
USE blogsite;

-- Create the books table
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    image_url VARCHAR(255),
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    author VARCHAR(100),
    category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO books (title, image_url, price)
VALUES
('Những người khốn khổ', 'images/books/nhungnguoikhonkho.png', 200000),
('Chiến tranh và hòa bình', 'images/books/loveandwar.png', 350000),
('Artificial Intelligence: A Modern Approach', 'images/books/AIMA.png', 1500000),
('Bố già', 'images/books/bo_gia.png', 180000);


CREATE TABLE Users (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    FullName VARCHAR(100) NOT NULL,
    UserName VARCHAR(50) UNIQUE NOT NULL,
    PasswordHash VARCHAR(255) NOT NULL,
    Email VARCHAR(100) UNIQUE NOT NULL,
    Phone VARCHAR(20),
    DateOfBirth DATE,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Favorites (
    UserID INT,
    BookID INT,
    DateAdded TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (UserID, BookID),
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (BookID) REFERENCES Books(ID) ON DELETE CASCADE
);

CREATE TABLE Add_To_Cart (
    UserID INT,
    BookID INT,
    Quantity INT DEFAULT 1,
    DateAdded TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (UserID, BookID),
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (BookID) REFERENCES Books(ID) ON DELETE CASCADE
);

CREATE TABLE Reviews (
    ReviewID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT,
    BookID INT,
    Rating INT CHECK (Rating BETWEEN 1 AND 5),
    Comment TEXT,
    ReviewDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE,
    FOREIGN KEY (BookID) REFERENCES Books(ID) ON DELETE CASCADE
);
