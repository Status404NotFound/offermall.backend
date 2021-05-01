CREATE TABLE offer
(
    offer_id INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    product_id INT(11) NOT NULL,
    base_stock INT(11) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    created_by INT(11) NOT NULL,
    updated_by INT(11) NOT NULL,
    base_item_cost DOUBLE NOT NULL,
    base_lead_cost DOUBLE NOT NULL,
    CONSTRAINT offer_ibfk_2 FOREIGN KEY (product_id) REFERENCES product (product_id),
    CONSTRAINT offer_ibfk_3 FOREIGN KEY (created_by) REFERENCES user (user_id),
    CONSTRAINT offer_ibfk_4 FOREIGN KEY (updated_by) REFERENCES user (user_id)
);
CREATE INDEX created_by_user_id ON offer (created_by, updated_by);
CREATE INDEX product_id ON offer (product_id);
CREATE INDEX updated_by_user_id ON offer (updated_by);