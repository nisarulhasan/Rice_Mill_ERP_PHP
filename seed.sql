USE rice_mill_erp;
INSERT INTO permissions(module,action,slug,description) VALUES
('Dashboard','read','dashboard_read','Dashboard access'),
('Users','read','user_read',''),('Users','create','user_create',''),('Users','update','user_update',''),('Users','delete','user_delete',''),
('Roles','read','role_read',''),('Roles','create','role_create',''),('Roles','update','role_update',''),('Roles','delete','role_delete',''),
('Company','read','company_read',''),('Company','update','company_update',''),('Bag Types','read','bag_read',''),('Bag Types','create','bag_create',''),('Bag Types','update','bag_update',''),('Bag Types','delete','bag_delete',''),
('UOM','read','uom_read',''),('UOM','create','uom_create',''),('UOM','update','uom_update',''),('UOM','delete','uom_delete',''),
('Warehouses','read','warehouse_read',''),('Warehouses','create','warehouse_create',''),('Warehouses','update','warehouse_update',''),('Warehouses','delete','warehouse_delete',''),
('Vendors','read','vendor_read',''),('Vendors','create','vendor_create',''),('Vendors','update','vendor_update',''),('Vendors','delete','vendor_delete',''),
('Customers','read','customer_read',''),('Customers','create','customer_create',''),('Customers','update','customer_update',''),('Customers','delete','customer_delete',''),
('Paddy','read','paddy_read',''),('Paddy','create','paddy_create',''),('Paddy','update','paddy_update',''),('Paddy','delete','paddy_delete',''),
('Rice','read','rice_read',''),('Rice','create','rice_create',''),('Rice','update','rice_update',''),('Rice','delete','rice_delete',''),
('Byproducts','read','byproduct_read',''),('Byproducts','create','byproduct_create',''),('Byproducts','update','byproduct_update',''),('Byproducts','delete','byproduct_delete',''),
('Purchase','read','purchase_read',''),('Purchase','create','purchase_create',''),('Purchase','update','purchase_update',''),('Purchase','delete','purchase_delete',''),('Purchase','export','purchase_export',''),
('Processing','read','processing_read',''),('Processing','create','processing_create',''),('Processing','update','processing_update',''),('Processing','delete','processing_delete',''),
('Stock','read','stock_read',''),('Stock','adjust','stock_adjust',''),
('Sales','read','sales_read',''),('Sales','create','sales_create',''),('Sales','update','sales_update',''),('Sales','delete','sales_delete',''),('Sales','export','sales_export',''),
('Accounting','read','accounting_read',''),('Accounting','create','accounting_create',''),('Accounting','update','accounting_update',''),('Accounting','delete','accounting_delete',''),
('Reports','read','report_read',''),('Backup','manage','backup_manage','');
INSERT INTO uom(name,short_name,conversion_to_kg) VALUES
('Kilogram','KG',1.0),('Quintal','Qtl',100.0),('Ton','Ton',1000.0),('Gram','gm',0.001),('Sack','Sack',50.0),('Bag','Bag',50.0);
INSERT INTO bag_types(name,weight_capacity_kg) VALUES
('Jute Bag 50kg',50),('PP Woven Bag 50kg',50),('PP Woven Bag 25kg',25),('Non-Woven Bag 20kg',20),('Non-Woven Bag 10kg',10),('Pouch 5kg',5),('Pouch 1kg',1);
INSERT INTO warehouses(name,warehouse_type,location) VALUES('Main Godown','general','Main Campus');
