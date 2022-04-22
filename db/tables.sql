CREATE TABLE orders (
  id INT PRIMARY KEY AUTO_INCREMENT,
  firstname TEXT NOT NULL,
  lastname TEXT NOT NULL,
  email TEXT NOT NULL,
  phone TEXT,
  street TEXT NOT NULL,
  number TEXT NOT NULL,
  plz INT(5) NOT NULL,
  city TEXT NOT NULL,
  coffee_amount INT NOT NULL,
  espresso_amount INT NOT NULL,
  espresso_mahlgrad TEXT NOT NULL
    CHECK (espresso_mahlgrad IN ('none', 'ungemahlen', 'mocca', 'fein', 'mittel', 'grob')),
  ship TEXT NOT NULL
    CHECK (ship IN ('hermes', 'personal', 'officepost', 'self')),
  pay TEXT NOT NULL,
    CHECK (pay IN ('cash', 'transfer')),
  office_number TEXT,
  comment TEXT,
  date DATETIME NOT NULL DEFAULT NOW()
);