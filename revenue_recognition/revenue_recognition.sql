CREATE SCHEMA ep_demo; -- enterprise patterns schema

CREATE TABLE ep_demo.products (
    id serial primary key, 
    name varchar, 
    type varchar
);

CREATE TABLE ep_demo.contracts (
    id serial primary key, 
    product int, 
    revenue numeric(7,2),
    date_signed date
);

CREATE TABLE ep_demo.revenueRecognitions (
    contract bigserial, 
    amount numeric(7,2),
    recognized_on date, 
    CONSTRAINT contract_recognized_on PRIMARY KEY (contract, recognized_on)
);
