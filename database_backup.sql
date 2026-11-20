--
-- PostgreSQL database dump
--

\restrict 3x1DJdXZqqW6fQenB72JcABeQSF0PcoUkAQ5coo2vM6eKTfaOCHnhpfhGoqd7gg

-- Dumped from database version 18.6
-- Dumped by pg_dump version 18.6

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

ALTER TABLE IF EXISTS ONLY inventory.users DROP CONSTRAINT IF EXISTS users_employee_id_fkey;
ALTER TABLE IF EXISTS ONLY inventory.stock_movements DROP CONSTRAINT IF EXISTS stock_movements_product_id_fkey;
ALTER TABLE IF EXISTS ONLY inventory.stock_movements DROP CONSTRAINT IF EXISTS stock_movements_performed_by_fkey;
ALTER TABLE IF EXISTS ONLY inventory.purchase_orders DROP CONSTRAINT IF EXISTS purchase_orders_supplier_id_fkey;
ALTER TABLE IF EXISTS ONLY inventory.purchase_orders DROP CONSTRAINT IF EXISTS purchase_orders_created_by_fkey;
ALTER TABLE IF EXISTS ONLY inventory.purchase_order_items DROP CONSTRAINT IF EXISTS purchase_order_items_purchase_order_id_fkey;
ALTER TABLE IF EXISTS ONLY inventory.purchase_order_items DROP CONSTRAINT IF EXISTS purchase_order_items_product_id_fkey;
ALTER TABLE IF EXISTS ONLY inventory.products DROP CONSTRAINT IF EXISTS products_supplier_id_fkey;
ALTER TABLE IF EXISTS ONLY inventory.products DROP CONSTRAINT IF EXISTS products_category_id_fkey;
ALTER TABLE IF EXISTS ONLY eis.users DROP CONSTRAINT IF EXISTS users_employee_id_fkey;
ALTER TABLE IF EXISTS ONLY eis.positions DROP CONSTRAINT IF EXISTS positions_department_id_fkey;
ALTER TABLE IF EXISTS ONLY eis.payroll DROP CONSTRAINT IF EXISTS payroll_employee_id_fkey;
ALTER TABLE IF EXISTS ONLY eis.leave_requests DROP CONSTRAINT IF EXISTS leave_requests_leave_type_id_fkey;
ALTER TABLE IF EXISTS ONLY eis.leave_requests DROP CONSTRAINT IF EXISTS leave_requests_employee_id_fkey;
ALTER TABLE IF EXISTS ONLY eis.leave_requests DROP CONSTRAINT IF EXISTS leave_requests_approved_by_fkey;
ALTER TABLE IF EXISTS ONLY eis.employees DROP CONSTRAINT IF EXISTS employees_position_id_fkey;
ALTER TABLE IF EXISTS ONLY eis.employees DROP CONSTRAINT IF EXISTS employees_department_id_fkey;
ALTER TABLE IF EXISTS ONLY eis.emergency_contacts DROP CONSTRAINT IF EXISTS emergency_contacts_employee_id_fkey;
ALTER TABLE IF EXISTS ONLY eis.attendance DROP CONSTRAINT IF EXISTS attendance_employee_id_fkey;
ALTER TABLE IF EXISTS ONLY canteen.users DROP CONSTRAINT IF EXISTS users_employee_id_fkey;
ALTER TABLE IF EXISTS ONLY canteen.shifts DROP CONSTRAINT IF EXISTS shifts_cashier_id_fkey;
ALTER TABLE IF EXISTS ONLY canteen.sales DROP CONSTRAINT IF EXISTS sales_voided_by_fkey;
ALTER TABLE IF EXISTS ONLY canteen.sales DROP CONSTRAINT IF EXISTS sales_shift_id_fkey;
ALTER TABLE IF EXISTS ONLY canteen.sales DROP CONSTRAINT IF EXISTS sales_cashier_id_fkey;
ALTER TABLE IF EXISTS ONLY canteen.sale_items DROP CONSTRAINT IF EXISTS sale_items_sale_id_fkey;
ALTER TABLE IF EXISTS ONLY canteen.sale_items DROP CONSTRAINT IF EXISTS sale_items_menu_item_id_fkey;
ALTER TABLE IF EXISTS ONLY canteen.payments DROP CONSTRAINT IF EXISTS payments_sale_id_fkey;
ALTER TABLE IF EXISTS ONLY canteen.menu_items DROP CONSTRAINT IF EXISTS menu_items_product_id_fkey;
ALTER TABLE IF EXISTS ONLY canteen.menu_items DROP CONSTRAINT IF EXISTS menu_items_category_id_fkey;
DROP INDEX IF EXISTS inventory.idx_products_category;
DROP INDEX IF EXISTS inventory.idx_movements_product;
DROP INDEX IF EXISTS canteen.one_open_shift_per_cashier;
DROP INDEX IF EXISTS canteen.idx_sales_sold_at;
DROP INDEX IF EXISTS canteen.idx_sales_cashier;
DROP INDEX IF EXISTS canteen.idx_sale_items_sale;
ALTER TABLE IF EXISTS ONLY inventory.users DROP CONSTRAINT IF EXISTS users_username_key;
ALTER TABLE IF EXISTS ONLY inventory.users DROP CONSTRAINT IF EXISTS users_pkey;
ALTER TABLE IF EXISTS ONLY inventory.users DROP CONSTRAINT IF EXISTS users_employee_id_key;
ALTER TABLE IF EXISTS ONLY inventory.suppliers DROP CONSTRAINT IF EXISTS suppliers_pkey;
ALTER TABLE IF EXISTS ONLY inventory.suppliers DROP CONSTRAINT IF EXISTS suppliers_name_key;
ALTER TABLE IF EXISTS ONLY inventory.stock_movements DROP CONSTRAINT IF EXISTS stock_movements_pkey;
ALTER TABLE IF EXISTS ONLY inventory.purchase_orders DROP CONSTRAINT IF EXISTS purchase_orders_po_number_key;
ALTER TABLE IF EXISTS ONLY inventory.purchase_orders DROP CONSTRAINT IF EXISTS purchase_orders_pkey;
ALTER TABLE IF EXISTS ONLY inventory.purchase_order_items DROP CONSTRAINT IF EXISTS purchase_order_items_purchase_order_id_product_id_key;
ALTER TABLE IF EXISTS ONLY inventory.purchase_order_items DROP CONSTRAINT IF EXISTS purchase_order_items_pkey;
ALTER TABLE IF EXISTS ONLY inventory.products DROP CONSTRAINT IF EXISTS products_sku_key;
ALTER TABLE IF EXISTS ONLY inventory.products DROP CONSTRAINT IF EXISTS products_pkey;
ALTER TABLE IF EXISTS ONLY inventory.products DROP CONSTRAINT IF EXISTS products_barcode_key;
ALTER TABLE IF EXISTS ONLY inventory.categories DROP CONSTRAINT IF EXISTS categories_pkey;
ALTER TABLE IF EXISTS ONLY inventory.categories DROP CONSTRAINT IF EXISTS categories_name_key;
ALTER TABLE IF EXISTS ONLY eis.users DROP CONSTRAINT IF EXISTS users_username_key;
ALTER TABLE IF EXISTS ONLY eis.users DROP CONSTRAINT IF EXISTS users_pkey;
ALTER TABLE IF EXISTS ONLY eis.users DROP CONSTRAINT IF EXISTS users_employee_id_key;
ALTER TABLE IF EXISTS ONLY eis.positions DROP CONSTRAINT IF EXISTS positions_pkey;
ALTER TABLE IF EXISTS ONLY eis.positions DROP CONSTRAINT IF EXISTS positions_department_id_title_key;
ALTER TABLE IF EXISTS ONLY eis.payroll DROP CONSTRAINT IF EXISTS payroll_pkey;
ALTER TABLE IF EXISTS ONLY eis.payroll DROP CONSTRAINT IF EXISTS payroll_employee_id_period_start_period_end_key;
ALTER TABLE IF EXISTS ONLY eis.leave_types DROP CONSTRAINT IF EXISTS leave_types_pkey;
ALTER TABLE IF EXISTS ONLY eis.leave_types DROP CONSTRAINT IF EXISTS leave_types_name_key;
ALTER TABLE IF EXISTS ONLY eis.leave_requests DROP CONSTRAINT IF EXISTS leave_requests_pkey;
ALTER TABLE IF EXISTS ONLY eis.employees DROP CONSTRAINT IF EXISTS employees_pkey;
ALTER TABLE IF EXISTS ONLY eis.employees DROP CONSTRAINT IF EXISTS employees_employee_no_key;
ALTER TABLE IF EXISTS ONLY eis.employees DROP CONSTRAINT IF EXISTS employees_email_key;
ALTER TABLE IF EXISTS ONLY eis.emergency_contacts DROP CONSTRAINT IF EXISTS emergency_contacts_pkey;
ALTER TABLE IF EXISTS ONLY eis.departments DROP CONSTRAINT IF EXISTS departments_pkey;
ALTER TABLE IF EXISTS ONLY eis.departments DROP CONSTRAINT IF EXISTS departments_name_key;
ALTER TABLE IF EXISTS ONLY eis.attendance DROP CONSTRAINT IF EXISTS attendance_pkey;
ALTER TABLE IF EXISTS ONLY eis.attendance DROP CONSTRAINT IF EXISTS attendance_employee_id_work_date_key;
ALTER TABLE IF EXISTS ONLY canteen.users DROP CONSTRAINT IF EXISTS users_username_key;
ALTER TABLE IF EXISTS ONLY canteen.users DROP CONSTRAINT IF EXISTS users_pkey;
ALTER TABLE IF EXISTS ONLY canteen.users DROP CONSTRAINT IF EXISTS users_employee_id_key;
ALTER TABLE IF EXISTS ONLY canteen.shifts DROP CONSTRAINT IF EXISTS shifts_pkey;
ALTER TABLE IF EXISTS ONLY canteen.sales DROP CONSTRAINT IF EXISTS sales_receipt_no_key;
ALTER TABLE IF EXISTS ONLY canteen.sales DROP CONSTRAINT IF EXISTS sales_pkey;
ALTER TABLE IF EXISTS ONLY canteen.sale_items DROP CONSTRAINT IF EXISTS sale_items_pkey;
ALTER TABLE IF EXISTS ONLY canteen.payments DROP CONSTRAINT IF EXISTS payments_sale_id_key;
ALTER TABLE IF EXISTS ONLY canteen.payments DROP CONSTRAINT IF EXISTS payments_pkey;
ALTER TABLE IF EXISTS ONLY canteen.menu_items DROP CONSTRAINT IF EXISTS menu_items_product_id_key;
ALTER TABLE IF EXISTS ONLY canteen.menu_items DROP CONSTRAINT IF EXISTS menu_items_pkey;
ALTER TABLE IF EXISTS ONLY canteen.menu_categories DROP CONSTRAINT IF EXISTS menu_categories_pkey;
ALTER TABLE IF EXISTS ONLY canteen.menu_categories DROP CONSTRAINT IF EXISTS menu_categories_name_key;
DROP TABLE IF EXISTS inventory.users;
DROP TABLE IF EXISTS inventory.suppliers;
DROP TABLE IF EXISTS inventory.stock_movements;
DROP TABLE IF EXISTS inventory.purchase_orders;
DROP TABLE IF EXISTS inventory.purchase_order_items;
DROP TABLE IF EXISTS inventory.products;
DROP TABLE IF EXISTS inventory.categories;
DROP TABLE IF EXISTS eis.users;
DROP TABLE IF EXISTS eis.positions;
DROP TABLE IF EXISTS eis.payroll;
DROP TABLE IF EXISTS eis.leave_types;
DROP TABLE IF EXISTS eis.leave_requests;
DROP TABLE IF EXISTS eis.employees;
DROP TABLE IF EXISTS eis.emergency_contacts;
DROP TABLE IF EXISTS eis.departments;
DROP TABLE IF EXISTS eis.attendance;
DROP TABLE IF EXISTS canteen.users;
DROP TABLE IF EXISTS canteen.shifts;
DROP TABLE IF EXISTS canteen.sales;
DROP TABLE IF EXISTS canteen.sale_items;
DROP TABLE IF EXISTS canteen.payments;
DROP TABLE IF EXISTS canteen.menu_items;
DROP TABLE IF EXISTS canteen.menu_categories;
DROP FUNCTION IF EXISTS inventory.canteen_stock_change(p_product_id bigint, p_change integer, p_sale_id bigint, p_reason text);
DROP SCHEMA IF EXISTS inventory;
DROP SCHEMA IF EXISTS eis;
DROP SCHEMA IF EXISTS canteen;
--
-- Name: canteen; Type: SCHEMA; Schema: -; Owner: canteen_user
--

CREATE SCHEMA canteen;


ALTER SCHEMA canteen OWNER TO canteen_user;

--
-- Name: eis; Type: SCHEMA; Schema: -; Owner: eis_user
--

CREATE SCHEMA eis;


ALTER SCHEMA eis OWNER TO eis_user;

--
-- Name: inventory; Type: SCHEMA; Schema: -; Owner: inventory_user
--

CREATE SCHEMA inventory;


ALTER SCHEMA inventory OWNER TO inventory_user;

--
-- Name: canteen_stock_change(bigint, integer, bigint, text); Type: FUNCTION; Schema: inventory; Owner: inventory_user
--

CREATE FUNCTION inventory.canteen_stock_change(p_product_id bigint, p_change integer, p_sale_id bigint, p_reason text DEFAULT 'Canteen POS'::text) RETURNS void
    LANGUAGE plpgsql SECURITY DEFINER
    SET search_path TO 'inventory', 'pg_temp'
    AS $$
DECLARE v_user BIGINT;
BEGIN
  IF p_change = 0 THEN
    RAISE EXCEPTION 'Stock change cannot be 0';
  END IF;

  SELECT id INTO v_user FROM users WHERE username = 'canteen_system';
  IF v_user IS NULL THEN
    RAISE EXCEPTION 'System user canteen_system is missing';
  END IF;

  -- fails automatically if stock would go below 0 (CHECK on quantity_on_hand)
  UPDATE products
     SET quantity_on_hand = quantity_on_hand + p_change, updated_at = now()
   WHERE id = p_product_id;
  IF NOT FOUND THEN
    RAISE EXCEPTION 'Product % not found', p_product_id;
  END IF;

  INSERT INTO stock_movements
    (product_id, movement_type, quantity, reference_type, reference_id, reason, performed_by)
  VALUES
    (p_product_id,
     CASE WHEN p_change < 0 THEN 'out' ELSE 'in' END,
     p_change, 'canteen_sale', p_sale_id, p_reason, v_user);
END;
$$;


ALTER FUNCTION inventory.canteen_stock_change(p_product_id bigint, p_change integer, p_sale_id bigint, p_reason text) OWNER TO inventory_user;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: menu_categories; Type: TABLE; Schema: canteen; Owner: canteen_user
--

CREATE TABLE canteen.menu_categories (
    id bigint NOT NULL,
    name character varying(100) NOT NULL,
    sort_order integer DEFAULT 0 NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL
);


ALTER TABLE canteen.menu_categories OWNER TO canteen_user;

--
-- Name: menu_categories_id_seq; Type: SEQUENCE; Schema: canteen; Owner: canteen_user
--

ALTER TABLE canteen.menu_categories ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME canteen.menu_categories_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: menu_items; Type: TABLE; Schema: canteen; Owner: canteen_user
--

CREATE TABLE canteen.menu_items (
    id bigint NOT NULL,
    category_id bigint NOT NULL,
    product_id bigint,
    name character varying(150) NOT NULL,
    description text,
    price numeric(10,2) NOT NULL,
    is_available boolean DEFAULT true NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT menu_items_price_check CHECK ((price >= (0)::numeric))
);


ALTER TABLE canteen.menu_items OWNER TO canteen_user;

--
-- Name: menu_items_id_seq; Type: SEQUENCE; Schema: canteen; Owner: canteen_user
--

ALTER TABLE canteen.menu_items ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME canteen.menu_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: payments; Type: TABLE; Schema: canteen; Owner: canteen_user
--

CREATE TABLE canteen.payments (
    id bigint NOT NULL,
    sale_id bigint NOT NULL,
    method character varying(20) DEFAULT 'cash'::character varying NOT NULL,
    amount_tendered numeric(10,2) NOT NULL,
    change_given numeric(10,2) DEFAULT 0 NOT NULL,
    reference_no character varying(50),
    paid_at timestamp without time zone DEFAULT now() NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT payments_amount_tendered_check CHECK ((amount_tendered >= (0)::numeric)),
    CONSTRAINT payments_change_given_check CHECK ((change_given >= (0)::numeric)),
    CONSTRAINT payments_method_check CHECK (((method)::text = ANY ((ARRAY['cash'::character varying, 'gcash'::character varying, 'other'::character varying])::text[])))
);


ALTER TABLE canteen.payments OWNER TO canteen_user;

--
-- Name: payments_id_seq; Type: SEQUENCE; Schema: canteen; Owner: canteen_user
--

ALTER TABLE canteen.payments ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME canteen.payments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: sale_items; Type: TABLE; Schema: canteen; Owner: canteen_user
--

CREATE TABLE canteen.sale_items (
    id bigint NOT NULL,
    sale_id bigint NOT NULL,
    menu_item_id bigint NOT NULL,
    item_name character varying(150) NOT NULL,
    unit_price numeric(10,2) NOT NULL,
    quantity integer NOT NULL,
    line_total numeric(10,2) GENERATED ALWAYS AS ((unit_price * (quantity)::numeric)) STORED,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT sale_items_quantity_check CHECK ((quantity > 0)),
    CONSTRAINT sale_items_unit_price_check CHECK ((unit_price >= (0)::numeric))
);


ALTER TABLE canteen.sale_items OWNER TO canteen_user;

--
-- Name: sale_items_id_seq; Type: SEQUENCE; Schema: canteen; Owner: canteen_user
--

ALTER TABLE canteen.sale_items ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME canteen.sale_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: sales; Type: TABLE; Schema: canteen; Owner: canteen_user
--

CREATE TABLE canteen.sales (
    id bigint NOT NULL,
    receipt_no character varying(30) NOT NULL,
    shift_id bigint NOT NULL,
    cashier_id bigint NOT NULL,
    order_type character varying(20) DEFAULT 'dine_in'::character varying NOT NULL,
    subtotal numeric(10,2) NOT NULL,
    discount numeric(10,2) DEFAULT 0 NOT NULL,
    total numeric(10,2) GENERATED ALWAYS AS ((subtotal - discount)) STORED,
    status character varying(20) DEFAULT 'completed'::character varying NOT NULL,
    notes text,
    sold_at timestamp without time zone DEFAULT now() NOT NULL,
    voided_by bigint,
    voided_at timestamp without time zone,
    void_reason text,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT sales_check CHECK ((discount <= subtotal)),
    CONSTRAINT sales_check1 CHECK ((((status)::text <> 'voided'::text) OR ((voided_by IS NOT NULL) AND (voided_at IS NOT NULL)))),
    CONSTRAINT sales_discount_check CHECK ((discount >= (0)::numeric)),
    CONSTRAINT sales_order_type_check CHECK (((order_type)::text = ANY ((ARRAY['dine_in'::character varying, 'take_out'::character varying])::text[]))),
    CONSTRAINT sales_status_check CHECK (((status)::text = ANY ((ARRAY['completed'::character varying, 'voided'::character varying])::text[]))),
    CONSTRAINT sales_subtotal_check CHECK ((subtotal >= (0)::numeric))
);


ALTER TABLE canteen.sales OWNER TO canteen_user;

--
-- Name: sales_id_seq; Type: SEQUENCE; Schema: canteen; Owner: canteen_user
--

ALTER TABLE canteen.sales ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME canteen.sales_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: shifts; Type: TABLE; Schema: canteen; Owner: canteen_user
--

CREATE TABLE canteen.shifts (
    id bigint NOT NULL,
    cashier_id bigint NOT NULL,
    opened_at timestamp without time zone DEFAULT now() NOT NULL,
    closed_at timestamp without time zone,
    opening_cash numeric(10,2) DEFAULT 0 NOT NULL,
    closing_cash numeric(10,2),
    status character varying(20) DEFAULT 'open'::character varying NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT shifts_status_check CHECK (((status)::text = ANY ((ARRAY['open'::character varying, 'closed'::character varying])::text[])))
);


ALTER TABLE canteen.shifts OWNER TO canteen_user;

--
-- Name: shifts_id_seq; Type: SEQUENCE; Schema: canteen; Owner: canteen_user
--

ALTER TABLE canteen.shifts ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME canteen.shifts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: users; Type: TABLE; Schema: canteen; Owner: canteen_user
--

CREATE TABLE canteen.users (
    id bigint NOT NULL,
    employee_id bigint,
    name character varying(150) NOT NULL,
    username character varying(50) NOT NULL,
    password character varying(255) NOT NULL,
    role character varying(20) DEFAULT 'cashier'::character varying NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    last_login_at timestamp without time zone,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT users_check CHECK ((((role)::text <> 'cashier'::text) OR (employee_id IS NOT NULL))),
    CONSTRAINT users_role_check CHECK (((role)::text = ANY ((ARRAY['admin'::character varying, 'cashier'::character varying])::text[])))
);


ALTER TABLE canteen.users OWNER TO canteen_user;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: canteen; Owner: canteen_user
--

ALTER TABLE canteen.users ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME canteen.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: attendance; Type: TABLE; Schema: eis; Owner: eis_user
--

CREATE TABLE eis.attendance (
    id bigint NOT NULL,
    employee_id bigint NOT NULL,
    work_date date NOT NULL,
    time_in time without time zone,
    time_out time without time zone,
    status character varying(20) DEFAULT 'present'::character varying NOT NULL,
    remarks text,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT attendance_status_check CHECK (((status)::text = ANY ((ARRAY['present'::character varying, 'absent'::character varying, 'late'::character varying, 'on_leave'::character varying])::text[])))
);


ALTER TABLE eis.attendance OWNER TO eis_user;

--
-- Name: attendance_id_seq; Type: SEQUENCE; Schema: eis; Owner: eis_user
--

ALTER TABLE eis.attendance ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME eis.attendance_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: departments; Type: TABLE; Schema: eis; Owner: eis_user
--

CREATE TABLE eis.departments (
    id bigint NOT NULL,
    name character varying(100) NOT NULL,
    description text,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL
);


ALTER TABLE eis.departments OWNER TO eis_user;

--
-- Name: departments_id_seq; Type: SEQUENCE; Schema: eis; Owner: eis_user
--

ALTER TABLE eis.departments ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME eis.departments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: emergency_contacts; Type: TABLE; Schema: eis; Owner: eis_user
--

CREATE TABLE eis.emergency_contacts (
    id bigint NOT NULL,
    employee_id bigint NOT NULL,
    name character varying(150) NOT NULL,
    relationship character varying(50) NOT NULL,
    phone character varying(20) NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL
);


ALTER TABLE eis.emergency_contacts OWNER TO eis_user;

--
-- Name: emergency_contacts_id_seq; Type: SEQUENCE; Schema: eis; Owner: eis_user
--

ALTER TABLE eis.emergency_contacts ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME eis.emergency_contacts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: employees; Type: TABLE; Schema: eis; Owner: eis_user
--

CREATE TABLE eis.employees (
    id bigint NOT NULL,
    employee_no character varying(20) NOT NULL,
    first_name character varying(100) NOT NULL,
    middle_name character varying(100),
    last_name character varying(100) NOT NULL,
    gender character varying(10),
    birthdate date,
    civil_status character varying(20),
    email character varying(150),
    phone character varying(20),
    address text,
    department_id bigint NOT NULL,
    position_id bigint NOT NULL,
    date_hired date NOT NULL,
    employment_type character varying(20) DEFAULT 'Regular'::character varying NOT NULL,
    status character varying(20) DEFAULT 'active'::character varying NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT employees_civil_status_check CHECK (((civil_status)::text = ANY ((ARRAY['Single'::character varying, 'Married'::character varying, 'Widowed'::character varying, 'Separated'::character varying])::text[]))),
    CONSTRAINT employees_employment_type_check CHECK (((employment_type)::text = ANY ((ARRAY['Regular'::character varying, 'Contractual'::character varying, 'Part-time'::character varying])::text[]))),
    CONSTRAINT employees_gender_check CHECK (((gender)::text = ANY ((ARRAY['Male'::character varying, 'Female'::character varying])::text[]))),
    CONSTRAINT employees_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'inactive'::character varying, 'resigned'::character varying, 'terminated'::character varying])::text[])))
);


ALTER TABLE eis.employees OWNER TO eis_user;

--
-- Name: employees_id_seq; Type: SEQUENCE; Schema: eis; Owner: eis_user
--

ALTER TABLE eis.employees ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME eis.employees_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: leave_requests; Type: TABLE; Schema: eis; Owner: eis_user
--

CREATE TABLE eis.leave_requests (
    id bigint NOT NULL,
    employee_id bigint NOT NULL,
    leave_type_id bigint NOT NULL,
    start_date date NOT NULL,
    end_date date NOT NULL,
    reason text,
    status character varying(20) DEFAULT 'pending'::character varying NOT NULL,
    approved_by bigint,
    decided_at timestamp without time zone,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT leave_requests_check CHECK ((end_date >= start_date)),
    CONSTRAINT leave_requests_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'approved'::character varying, 'rejected'::character varying, 'cancelled'::character varying])::text[])))
);


ALTER TABLE eis.leave_requests OWNER TO eis_user;

--
-- Name: leave_requests_id_seq; Type: SEQUENCE; Schema: eis; Owner: eis_user
--

ALTER TABLE eis.leave_requests ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME eis.leave_requests_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: leave_types; Type: TABLE; Schema: eis; Owner: eis_user
--

CREATE TABLE eis.leave_types (
    id bigint NOT NULL,
    name character varying(50) NOT NULL,
    days_allowed integer DEFAULT 0 NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL
);


ALTER TABLE eis.leave_types OWNER TO eis_user;

--
-- Name: leave_types_id_seq; Type: SEQUENCE; Schema: eis; Owner: eis_user
--

ALTER TABLE eis.leave_types ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME eis.leave_types_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: payroll; Type: TABLE; Schema: eis; Owner: eis_user
--

CREATE TABLE eis.payroll (
    id bigint NOT NULL,
    employee_id bigint NOT NULL,
    period_start date NOT NULL,
    period_end date NOT NULL,
    basic_pay numeric(10,2) NOT NULL,
    allowances numeric(10,2) DEFAULT 0 NOT NULL,
    deductions numeric(10,2) DEFAULT 0 NOT NULL,
    net_pay numeric(10,2) GENERATED ALWAYS AS (((basic_pay + allowances) - deductions)) STORED,
    status character varying(20) DEFAULT 'draft'::character varying NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT payroll_check CHECK ((period_end >= period_start)),
    CONSTRAINT payroll_status_check CHECK (((status)::text = ANY ((ARRAY['draft'::character varying, 'released'::character varying])::text[])))
);


ALTER TABLE eis.payroll OWNER TO eis_user;

--
-- Name: payroll_id_seq; Type: SEQUENCE; Schema: eis; Owner: eis_user
--

ALTER TABLE eis.payroll ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME eis.payroll_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: positions; Type: TABLE; Schema: eis; Owner: eis_user
--

CREATE TABLE eis.positions (
    id bigint NOT NULL,
    department_id bigint NOT NULL,
    title character varying(100) NOT NULL,
    base_salary numeric(10,2) DEFAULT 0 NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL
);


ALTER TABLE eis.positions OWNER TO eis_user;

--
-- Name: positions_id_seq; Type: SEQUENCE; Schema: eis; Owner: eis_user
--

ALTER TABLE eis.positions ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME eis.positions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: users; Type: TABLE; Schema: eis; Owner: eis_user
--

CREATE TABLE eis.users (
    id bigint NOT NULL,
    employee_id bigint NOT NULL,
    username character varying(50) NOT NULL,
    password character varying(255) NOT NULL,
    role character varying(20) DEFAULT 'employee'::character varying NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    last_login_at timestamp without time zone,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT users_role_check CHECK (((role)::text = ANY ((ARRAY['admin'::character varying, 'hr'::character varying, 'employee'::character varying])::text[])))
);


ALTER TABLE eis.users OWNER TO eis_user;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: eis; Owner: eis_user
--

ALTER TABLE eis.users ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME eis.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: categories; Type: TABLE; Schema: inventory; Owner: inventory_user
--

CREATE TABLE inventory.categories (
    id bigint NOT NULL,
    name character varying(100) NOT NULL,
    description text,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL
);


ALTER TABLE inventory.categories OWNER TO inventory_user;

--
-- Name: categories_id_seq; Type: SEQUENCE; Schema: inventory; Owner: inventory_user
--

ALTER TABLE inventory.categories ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME inventory.categories_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: products; Type: TABLE; Schema: inventory; Owner: inventory_user
--

CREATE TABLE inventory.products (
    id bigint NOT NULL,
    sku character varying(50) NOT NULL,
    barcode character varying(50),
    name character varying(150) NOT NULL,
    description text,
    category_id bigint NOT NULL,
    supplier_id bigint,
    unit character varying(20) DEFAULT 'pc'::character varying NOT NULL,
    cost_price numeric(10,2) DEFAULT 0 NOT NULL,
    selling_price numeric(10,2) DEFAULT 0 NOT NULL,
    quantity_on_hand integer DEFAULT 0 NOT NULL,
    reorder_level integer DEFAULT 0 NOT NULL,
    status character varying(20) DEFAULT 'active'::character varying NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT products_cost_price_check CHECK ((cost_price >= (0)::numeric)),
    CONSTRAINT products_quantity_on_hand_check CHECK ((quantity_on_hand >= 0)),
    CONSTRAINT products_selling_price_check CHECK ((selling_price >= (0)::numeric)),
    CONSTRAINT products_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'inactive'::character varying])::text[])))
);


ALTER TABLE inventory.products OWNER TO inventory_user;

--
-- Name: products_id_seq; Type: SEQUENCE; Schema: inventory; Owner: inventory_user
--

ALTER TABLE inventory.products ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME inventory.products_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: purchase_order_items; Type: TABLE; Schema: inventory; Owner: inventory_user
--

CREATE TABLE inventory.purchase_order_items (
    id bigint NOT NULL,
    purchase_order_id bigint NOT NULL,
    product_id bigint NOT NULL,
    quantity integer NOT NULL,
    unit_cost numeric(10,2) NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT purchase_order_items_quantity_check CHECK ((quantity > 0)),
    CONSTRAINT purchase_order_items_unit_cost_check CHECK ((unit_cost >= (0)::numeric))
);


ALTER TABLE inventory.purchase_order_items OWNER TO inventory_user;

--
-- Name: purchase_order_items_id_seq; Type: SEQUENCE; Schema: inventory; Owner: inventory_user
--

ALTER TABLE inventory.purchase_order_items ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME inventory.purchase_order_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: purchase_orders; Type: TABLE; Schema: inventory; Owner: inventory_user
--

CREATE TABLE inventory.purchase_orders (
    id bigint NOT NULL,
    po_number character varying(30) NOT NULL,
    supplier_id bigint NOT NULL,
    order_date date DEFAULT CURRENT_DATE NOT NULL,
    expected_date date,
    status character varying(20) DEFAULT 'pending'::character varying NOT NULL,
    notes text,
    created_by bigint NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT purchase_orders_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'received'::character varying, 'cancelled'::character varying])::text[])))
);


ALTER TABLE inventory.purchase_orders OWNER TO inventory_user;

--
-- Name: purchase_orders_id_seq; Type: SEQUENCE; Schema: inventory; Owner: inventory_user
--

ALTER TABLE inventory.purchase_orders ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME inventory.purchase_orders_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: stock_movements; Type: TABLE; Schema: inventory; Owner: inventory_user
--

CREATE TABLE inventory.stock_movements (
    id bigint NOT NULL,
    product_id bigint NOT NULL,
    movement_type character varying(20) NOT NULL,
    quantity integer NOT NULL,
    reference_type character varying(30),
    reference_id bigint,
    reason text,
    performed_by bigint NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT stock_movements_check CHECK (((((movement_type)::text = 'in'::text) AND (quantity > 0)) OR (((movement_type)::text = 'out'::text) AND (quantity < 0)) OR (((movement_type)::text = 'adjustment'::text) AND (quantity <> 0)))),
    CONSTRAINT stock_movements_movement_type_check CHECK (((movement_type)::text = ANY ((ARRAY['in'::character varying, 'out'::character varying, 'adjustment'::character varying])::text[])))
);


ALTER TABLE inventory.stock_movements OWNER TO inventory_user;

--
-- Name: stock_movements_id_seq; Type: SEQUENCE; Schema: inventory; Owner: inventory_user
--

ALTER TABLE inventory.stock_movements ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME inventory.stock_movements_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: suppliers; Type: TABLE; Schema: inventory; Owner: inventory_user
--

CREATE TABLE inventory.suppliers (
    id bigint NOT NULL,
    name character varying(150) NOT NULL,
    contact_person character varying(150),
    phone character varying(20),
    email character varying(150),
    address text,
    status character varying(20) DEFAULT 'active'::character varying NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT suppliers_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'inactive'::character varying])::text[])))
);


ALTER TABLE inventory.suppliers OWNER TO inventory_user;

--
-- Name: suppliers_id_seq; Type: SEQUENCE; Schema: inventory; Owner: inventory_user
--

ALTER TABLE inventory.suppliers ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME inventory.suppliers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: users; Type: TABLE; Schema: inventory; Owner: inventory_user
--

CREATE TABLE inventory.users (
    id bigint NOT NULL,
    employee_id bigint,
    name character varying(150) NOT NULL,
    username character varying(50) NOT NULL,
    password character varying(255) NOT NULL,
    role character varying(20) DEFAULT 'staff'::character varying NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    last_login_at timestamp without time zone,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL,
    CONSTRAINT users_role_check CHECK (((role)::text = ANY ((ARRAY['admin'::character varying, 'staff'::character varying])::text[])))
);


ALTER TABLE inventory.users OWNER TO inventory_user;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: inventory; Owner: inventory_user
--

ALTER TABLE inventory.users ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME inventory.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Data for Name: menu_categories; Type: TABLE DATA; Schema: canteen; Owner: canteen_user
--

COPY canteen.menu_categories (id, name, sort_order, created_at, updated_at) FROM stdin;
1	Meals	1	2026-11-13 14:08:37.528128	2026-11-13 14:08:37.528128
2	Drinks	2	2026-11-13 14:08:37.528128	2026-11-13 14:08:37.528128
3	Snacks	3	2026-11-13 14:08:37.528128	2026-11-13 14:08:37.528128
4	Desserts	4	2026-11-19 11:14:04.967502	2026-11-19 11:14:04.967502
\.


--
-- Data for Name: menu_items; Type: TABLE DATA; Schema: canteen; Owner: canteen_user
--

COPY canteen.menu_items (id, category_id, product_id, name, description, price, is_available, created_at, updated_at) FROM stdin;
1	2	1	Bottled Water 500ml	\N	15.00	t	2026-11-13 14:08:37.528128	2026-11-13 14:08:37.528128
2	3	2	Chips	\N	20.00	t	2026-11-13 14:08:37.528128	2026-11-13 14:08:37.528128
3	1	4	Chicken Adobo with Rice	\N	55.00	t	2026-11-13 14:08:37.528128	2026-11-13 14:08:37.528128
4	1	5	Pork Sinigang with Rice	\N	80.00	t	2026-11-19 11:14:04.970631	2026-11-19 11:14:04.970631
5	1	6	Beef Caldereta with Rice	\N	95.00	t	2026-11-19 11:14:04.971763	2026-11-19 11:14:04.971763
6	3	7	Cheesy Lumpiang Shanghai (5 pcs)	\N	35.00	t	2026-11-19 11:14:04.972193	2026-11-19 11:14:04.972193
7	2	8	House Blend Iced Tea (16 oz)	\N	20.00	t	2026-11-19 11:14:04.972629	2026-11-19 11:14:04.972629
8	4	9	Special Halo-Halo	\N	50.00	t	2026-11-19 11:14:04.973039	2026-11-19 11:14:04.973039
\.


--
-- Data for Name: payments; Type: TABLE DATA; Schema: canteen; Owner: canteen_user
--

COPY canteen.payments (id, sale_id, method, amount_tendered, change_given, reference_no, paid_at, created_at, updated_at) FROM stdin;
1	1	cash	100.00	70.00	\N	2026-11-19 11:19:28	2026-11-19 11:19:28	2026-11-19 11:19:28
2	2	cash	50.00	10.00	\N	2026-11-20 14:56:14	2026-11-20 14:56:14	2026-11-20 14:56:14
\.


--
-- Data for Name: sale_items; Type: TABLE DATA; Schema: canteen; Owner: canteen_user
--

COPY canteen.sale_items (id, sale_id, menu_item_id, item_name, unit_price, quantity, created_at, updated_at) FROM stdin;
1	1	1	Bottled Water 500ml	15.00	2	2026-11-19 11:19:28	2026-11-19 11:19:28
2	2	2	Chips	20.00	2	2026-11-20 14:56:14	2026-11-20 14:56:14
\.


--
-- Data for Name: sales; Type: TABLE DATA; Schema: canteen; Owner: canteen_user
--

COPY canteen.sales (id, receipt_no, shift_id, cashier_id, order_type, subtotal, discount, status, notes, sold_at, voided_by, voided_at, void_reason, created_at, updated_at) FROM stdin;
1	RCP-20261119-0001	1	1	dine_in	30.00	0.00	voided	Test Student	2026-11-19 11:19:28	1	2026-11-19 11:19:28	Cashier Void & Refund	2026-11-19 11:19:28	2026-11-19 11:19:28
2	RCP-20261120-0001	2	12	dine_in	40.00	0.00	completed	Test Student	2026-11-20 14:56:14	\N	\N	\N	2026-11-20 14:56:14	2026-11-20 14:56:14
\.


--
-- Data for Name: shifts; Type: TABLE DATA; Schema: canteen; Owner: canteen_user
--

COPY canteen.shifts (id, cashier_id, opened_at, closed_at, opening_cash, closing_cash, status, created_at, updated_at) FROM stdin;
1	1	2026-11-19 11:13:10.716357	\N	1000.00	\N	open	2026-11-19 11:13:10.716357	2026-11-19 11:13:10.716357
2	12	2026-11-20 14:56:14	\N	0.00	\N	open	2026-11-20 14:56:14	2026-11-20 14:56:14
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: canteen; Owner: canteen_user
--

COPY canteen.users (id, employee_id, name, username, password, role, is_active, last_login_at, created_at, updated_at) FROM stdin;
1	1	Admin Cashier	admin@school.com	$2y$12$1xx454tkWF73K9WsthboJuExuDEQtNiUFiPfRSfKjKAlXMCGEnWL6	admin	t	\N	2026-11-19 11:11:08.790064	2026-11-19 11:11:08.790064
2	\N	Juan Dela Cruz	juan@gmail.com	$2y$12$.076kFqV.Nl0HxnDxTFDneW8LmzL0zahjyBhT0Cn9.JFhh8MpYsvO	admin	t	\N	2026-11-20 14:10:38	2026-11-20 14:10:38
3	12	Lorejo, Lian Zyrelle E.	llorejo	$2y$12$8T6M4D1lW8M/k8jFqWTvqOjBvAJPpwWQInqyIF2B7mrdKsbBKMzia	admin	t	\N	2026-11-20 14:23:08	2026-11-20 14:23:08
4	13	Pescante, Christal Pearl F.	cpescante	$2y$12$8T6M4D1lW8M/k8jFqWTvqOjBvAJPpwWQInqyIF2B7mrdKsbBKMzia	cashier	t	\N	2026-11-20 14:23:08	2026-11-20 14:23:08
5	14	Ramos, Carlos Jaime Roa	cramos	$2y$12$8T6M4D1lW8M/k8jFqWTvqOjBvAJPpwWQInqyIF2B7mrdKsbBKMzia	cashier	t	\N	2026-11-20 14:23:08	2026-11-20 14:23:08
6	15	Mauhay, Jhon Jefferson A.	jmauhay	$2y$12$8T6M4D1lW8M/k8jFqWTvqOjBvAJPpwWQInqyIF2B7mrdKsbBKMzia	cashier	t	\N	2026-11-20 14:23:08	2026-11-20 14:23:08
7	16	Manon-og, Maiah Reva B.	mmanonog	$2y$12$8T6M4D1lW8M/k8jFqWTvqOjBvAJPpwWQInqyIF2B7mrdKsbBKMzia	cashier	t	\N	2026-11-20 14:23:08	2026-11-20 14:23:08
\.


--
-- Data for Name: attendance; Type: TABLE DATA; Schema: eis; Owner: eis_user
--

COPY eis.attendance (id, employee_id, work_date, time_in, time_out, status, remarks, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: departments; Type: TABLE DATA; Schema: eis; Owner: eis_user
--

COPY eis.departments (id, name, description, created_at, updated_at) FROM stdin;
1	Canteen Services	School Canteen & Food Operations	2026-11-19 11:12:46.804818	2026-11-19 11:12:46.804818
2	Warehouse & Inventory Management	Centralized Inventory, Stock Control and Procurement	2026-11-20 14:23:08	2026-11-20 14:23:08
3	Human Resources & EIS	Employee Information and Personnel Management	2026-11-20 14:23:08	2026-11-20 14:23:08
\.


--
-- Data for Name: emergency_contacts; Type: TABLE DATA; Schema: eis; Owner: eis_user
--

COPY eis.emergency_contacts (id, employee_id, name, relationship, phone, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: employees; Type: TABLE DATA; Schema: eis; Owner: eis_user
--

COPY eis.employees (id, employee_no, first_name, middle_name, last_name, gender, birthdate, civil_status, email, phone, address, department_id, position_id, date_hired, employment_type, status, created_at, updated_at) FROM stdin;
1	EMP-2026-001	Admin	\N	Cashier	Male	\N	Single	\N	\N	\N	1	1	2026-11-19	Regular	active	2026-11-19 11:13:10.529416	2026-11-19 11:13:10.529416
2	EMP-2026-002	Edeva Joy	O.	Dybongco	Female	\N	Single	\N	\N	\N	3	6	2026-11-20	Regular	active	2026-11-20 14:23:08	2026-11-20 14:23:08
3	EMP-2026-003	Katrayne Princess	B.	Miranda	Female	\N	Single	\N	\N	\N	3	7	2026-11-20	Regular	active	2026-11-20 14:23:08	2026-11-20 14:23:08
4	EMP-2026-004	Almerande Jr.	P.	Pampilo	Male	\N	Single	\N	\N	\N	3	7	2026-11-20	Regular	active	2026-11-20 14:23:08	2026-11-20 14:23:08
5	EMP-2026-005	Sheila Marie	\N	Taladua	Female	\N	Single	\N	\N	\N	3	7	2026-11-20	Regular	active	2026-11-20 14:23:08	2026-11-20 14:23:08
6	EMP-2026-006	Denjzel	S.	Caguindangan	Male	\N	Single	\N	\N	\N	3	7	2026-11-20	Regular	active	2026-11-20 14:23:08	2026-11-20 14:23:08
7	EMP-2026-007	Marchelyn	S.	Manlaran	Female	\N	Single	\N	\N	\N	2	3	2026-11-20	Regular	active	2026-11-20 14:23:08	2026-11-20 14:23:08
8	EMP-2026-008	Sherylle	\N	Saramosing	Female	\N	Single	\N	\N	\N	2	4	2026-11-20	Regular	active	2026-11-20 14:23:08	2026-11-20 14:23:08
9	EMP-2026-009	Gogen	P.	Antipuesto	Male	\N	Single	\N	\N	\N	2	4	2026-11-20	Regular	active	2026-11-20 14:23:08	2026-11-20 14:23:08
10	EMP-2026-010	Jolly Hope	T.	Dancila	Female	\N	Single	\N	\N	\N	2	5	2026-11-20	Regular	active	2026-11-20 14:23:08	2026-11-20 14:23:08
11	EMP-2026-011	Alexander Josh	A.	David	Male	\N	Single	\N	\N	\N	2	4	2026-11-20	Regular	active	2026-11-20 14:23:08	2026-11-20 14:23:08
12	EMP-2026-012	Lian Zyrelle	E.	Lorejo	Female	\N	Single	\N	\N	\N	1	1	2026-11-20	Regular	active	2026-11-20 14:23:08	2026-11-20 14:23:08
13	EMP-2026-013	Christal Pearl	F.	Pescante	Female	\N	Single	\N	\N	\N	1	2	2026-11-20	Regular	active	2026-11-20 14:23:08	2026-11-20 14:23:08
14	EMP-2026-014	Carlos Jaime	Roa	Ramos	Male	\N	Single	\N	\N	\N	1	2	2026-11-20	Regular	active	2026-11-20 14:23:08	2026-11-20 14:23:08
15	EMP-2026-015	Jhon Jefferson	A.	Mauhay	Male	\N	Single	\N	\N	\N	1	2	2026-11-20	Regular	active	2026-11-20 14:23:08	2026-11-20 14:23:08
16	EMP-2026-016	Maiah Reva	B.	Manon-og	Female	\N	Single	\N	\N	\N	1	2	2026-11-20	Regular	active	2026-11-20 14:23:08	2026-11-20 14:23:08
\.


--
-- Data for Name: leave_requests; Type: TABLE DATA; Schema: eis; Owner: eis_user
--

COPY eis.leave_requests (id, employee_id, leave_type_id, start_date, end_date, reason, status, approved_by, decided_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: leave_types; Type: TABLE DATA; Schema: eis; Owner: eis_user
--

COPY eis.leave_types (id, name, days_allowed, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: payroll; Type: TABLE DATA; Schema: eis; Owner: eis_user
--

COPY eis.payroll (id, employee_id, period_start, period_end, basic_pay, allowances, deductions, status, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: positions; Type: TABLE DATA; Schema: eis; Owner: eis_user
--

COPY eis.positions (id, department_id, title, base_salary, created_at, updated_at) FROM stdin;
1	1	Head Cashier	25000.00	2026-11-19 11:12:46.80779	2026-11-19 11:12:46.80779
2	1	Canteen Cashier	18000.00	2026-11-20 14:23:08	2026-11-20 14:23:08
3	2	Inventory Manager	28000.00	2026-11-20 14:23:08	2026-11-20 14:23:08
4	2	Warehouse Custodian	20000.00	2026-11-20 14:23:08	2026-11-20 14:23:08
5	2	Procurement Officer	22000.00	2026-11-20 14:23:08	2026-11-20 14:23:08
6	3	HR Administrator	28000.00	2026-11-20 14:23:08	2026-11-20 14:23:08
7	3	HR Specialist	20000.00	2026-11-20 14:23:08	2026-11-20 14:23:08
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: eis; Owner: eis_user
--

COPY eis.users (id, employee_id, username, password, role, is_active, last_login_at, created_at, updated_at) FROM stdin;
1	2	edybongco	$2y$12$8T6M4D1lW8M/k8jFqWTvqOjBvAJPpwWQInqyIF2B7mrdKsbBKMzia	admin	t	\N	2026-11-20 14:23:08	2026-11-20 14:23:08
2	3	kmiranda	$2y$12$8T6M4D1lW8M/k8jFqWTvqOjBvAJPpwWQInqyIF2B7mrdKsbBKMzia	hr	t	\N	2026-11-20 14:23:08	2026-11-20 14:23:08
3	4	apampilo	$2y$12$8T6M4D1lW8M/k8jFqWTvqOjBvAJPpwWQInqyIF2B7mrdKsbBKMzia	employee	t	\N	2026-11-20 14:23:08	2026-11-20 14:23:08
4	5	staladua	$2y$12$8T6M4D1lW8M/k8jFqWTvqOjBvAJPpwWQInqyIF2B7mrdKsbBKMzia	employee	t	\N	2026-11-20 14:23:08	2026-11-20 14:23:08
5	6	dcaguindangan	$2y$12$8T6M4D1lW8M/k8jFqWTvqOjBvAJPpwWQInqyIF2B7mrdKsbBKMzia	employee	t	\N	2026-11-20 14:23:08	2026-11-20 14:23:08
\.


--
-- Data for Name: categories; Type: TABLE DATA; Schema: inventory; Owner: inventory_user
--

COPY inventory.categories (id, name, description, created_at, updated_at) FROM stdin;
1	Beverages	\N	2026-11-13 14:05:05.789571	2026-11-13 14:05:05.789571
2	Snacks	\N	2026-11-13 14:05:05.789571	2026-11-13 14:05:05.789571
3	Meals & Ingredients	\N	2026-11-13 14:05:05.789571	2026-11-13 14:05:05.789571
4	Meals	Main Canteen Meals	2026-11-19 11:13:50.291211	2026-11-19 11:13:50.291211
5	Desserts	Sweet treats and desserts	2026-11-19 11:13:50.293351	2026-11-19 11:13:50.293351
\.


--
-- Data for Name: products; Type: TABLE DATA; Schema: inventory; Owner: inventory_user
--

COPY inventory.products (id, sku, barcode, name, description, category_id, supplier_id, unit, cost_price, selling_price, quantity_on_hand, reorder_level, status, created_at, updated_at) FROM stdin;
7	SNK-002	\N	Cheesy Lumpiang Shanghai (5 pcs)	\N	2	3	pc	22.00	35.00	40	10	active	2026-11-19 11:14:04.96349	2026-11-20 14:05:25
1	BEV-001	\N	Bottled Water 500ml	\N	1	4	bottle	10.00	15.00	50	10	active	2026-11-13 14:05:05.789571	2026-11-20 14:05:25
8	BEV-002	\N	House Blend Iced Tea (16 oz)	\N	1	4	pc	10.00	20.00	50	10	active	2026-11-19 11:14:04.963892	2026-11-20 14:05:25
4	MEL-001	\N	Chicken Adobo with Rice	\N	4	1	pc	45.00	55.00	35	10	active	2026-11-19 11:14:04.957862	2026-11-20 14:05:25
5	MEL-002	\N	Pork Sinigang with Rice	\N	4	1	pc	60.00	80.00	25	10	active	2026-11-19 11:14:04.962669	2026-11-20 14:05:25
6	MEL-003	\N	Beef Caldereta with Rice	\N	4	1	pc	75.00	95.00	20	10	active	2026-11-19 11:14:04.963086	2026-11-20 14:05:25
9	DES-001	\N	Special Halo-Halo	\N	5	5	pc	35.00	50.00	25	10	active	2026-11-19 11:14:04.964248	2026-11-20 14:05:25
2	SNK-001	\N	Chips	\N	2	2	pack	12.00	20.00	38	10	active	2026-11-13 14:05:05.789571	2026-11-20 14:56:14
\.


--
-- Data for Name: purchase_order_items; Type: TABLE DATA; Schema: inventory; Owner: inventory_user
--

COPY inventory.purchase_order_items (id, purchase_order_id, product_id, quantity, unit_cost, created_at, updated_at) FROM stdin;
1	1	4	25	45.00	2026-11-20 14:12:22	2026-11-20 14:12:22
2	1	2	30	12.00	2026-11-20 14:12:22	2026-11-20 14:12:22
\.


--
-- Data for Name: purchase_orders; Type: TABLE DATA; Schema: inventory; Owner: inventory_user
--

COPY inventory.purchase_orders (id, po_number, supplier_id, order_date, expected_date, status, notes, created_by, created_at, updated_at) FROM stdin;
1	PO-20261120-A101	1	2026-11-20	2026-11-22	pending	Replenishment order for canteen lunch staples and pantry snacks.	1	2026-11-20 14:12:22	2026-11-20 14:12:22
\.


--
-- Data for Name: stock_movements; Type: TABLE DATA; Schema: inventory; Owner: inventory_user
--

COPY inventory.stock_movements (id, product_id, movement_type, quantity, reference_type, reference_id, reason, performed_by, created_at) FROM stdin;
2	1	out	-2	canteen_sale	1	POS Sale #RCP-20261119-0001	1	2026-11-19 11:19:28
3	1	in	2	canteen_sale_void	1	Voided Sale #RCP-20261119-0001	1	2026-11-19 11:19:28
4	2	out	-2	canteen_sale	2	POS Sale #RCP-20261120-0001	1	2026-11-20 14:56:14
5	2	in	10	purchase_order	999	Received PO #PO-TEST-001	2	2026-11-20 14:56:14
\.


--
-- Data for Name: suppliers; Type: TABLE DATA; Schema: inventory; Owner: inventory_user
--

COPY inventory.suppliers (id, name, contact_person, phone, email, address, status, created_at, updated_at) FROM stdin;
1	San Miguel Food and Beverage Inc.	Eduardo Mendoza	+63 2 8632 3000	sales@sanmiguel.com.ph	40 San Miguel Ave, Mandaluyong, Metro Manila	active	2026-11-20 14:05:19	2026-11-20 14:05:19
2	Universal Robina Corporation (URC)	Clarisse Santos	+63 2 8633 7631	orders@urc.com.ph	Tera Tower, Bridgetowne, Pasig City	active	2026-11-20 14:05:19	2026-11-20 14:05:19
3	Monde Nissin Philippines	Robert Tan	+63 2 7759 7500	distribution@mondenissin.com	Felix Reyes St., Balibago, Santa Rosa, Laguna	active	2026-11-20 14:05:19	2026-11-20 14:05:19
4	Nestlé Philippines Inc.	Maria Kristina Cruz	+63 2 8898 0001	enterprise@ph.nestle.com	31 Plaza Drive, Rockwell Center, Makati City	active	2026-11-20 14:05:19	2026-11-20 14:05:19
5	Mega Global Food Wholesaler	Antonio Yap	+63 2 8288 8888	sales@megaglobal.com.ph	Navotas Fish Port Complex, Navotas City	active	2026-11-20 14:05:19	2026-11-20 14:05:19
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: inventory; Owner: inventory_user
--

COPY inventory.users (id, employee_id, name, username, password, role, is_active, last_login_at, created_at, updated_at) FROM stdin;
1	\N	Canteen POS (system)	canteen_system	!	staff	f	\N	2026-11-13 14:08:05.040965	2026-11-13 14:08:05.040965
2	7	Manlaran, Marchelyn S.	mmanlaran	$2y$12$8T6M4D1lW8M/k8jFqWTvqOjBvAJPpwWQInqyIF2B7mrdKsbBKMzia	admin	t	\N	2026-11-20 14:23:08	2026-11-20 14:23:08
3	8	Saramosing, Sherylle	ssaramosing	$2y$12$8T6M4D1lW8M/k8jFqWTvqOjBvAJPpwWQInqyIF2B7mrdKsbBKMzia	staff	t	\N	2026-11-20 14:23:08	2026-11-20 14:23:08
4	9	Antipuesto, Gogen P.	gantipuesto	$2y$12$8T6M4D1lW8M/k8jFqWTvqOjBvAJPpwWQInqyIF2B7mrdKsbBKMzia	staff	t	\N	2026-11-20 14:23:08	2026-11-20 14:23:08
5	10	Dancila, Jolly Hope T.	jdancila	$2y$12$8T6M4D1lW8M/k8jFqWTvqOjBvAJPpwWQInqyIF2B7mrdKsbBKMzia	staff	t	\N	2026-11-20 14:23:08	2026-11-20 14:23:08
6	11	David, Alexander Josh A.	adavid	$2y$12$8T6M4D1lW8M/k8jFqWTvqOjBvAJPpwWQInqyIF2B7mrdKsbBKMzia	staff	t	\N	2026-11-20 14:23:08	2026-11-20 14:23:08
\.


--
-- Name: menu_categories_id_seq; Type: SEQUENCE SET; Schema: canteen; Owner: canteen_user
--

SELECT pg_catalog.setval('canteen.menu_categories_id_seq', 4, true);


--
-- Name: menu_items_id_seq; Type: SEQUENCE SET; Schema: canteen; Owner: canteen_user
--

SELECT pg_catalog.setval('canteen.menu_items_id_seq', 8, true);


--
-- Name: payments_id_seq; Type: SEQUENCE SET; Schema: canteen; Owner: canteen_user
--

SELECT pg_catalog.setval('canteen.payments_id_seq', 2, true);


--
-- Name: sale_items_id_seq; Type: SEQUENCE SET; Schema: canteen; Owner: canteen_user
--

SELECT pg_catalog.setval('canteen.sale_items_id_seq', 2, true);


--
-- Name: sales_id_seq; Type: SEQUENCE SET; Schema: canteen; Owner: canteen_user
--

SELECT pg_catalog.setval('canteen.sales_id_seq', 2, true);


--
-- Name: shifts_id_seq; Type: SEQUENCE SET; Schema: canteen; Owner: canteen_user
--

SELECT pg_catalog.setval('canteen.shifts_id_seq', 2, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: canteen; Owner: canteen_user
--

SELECT pg_catalog.setval('canteen.users_id_seq', 7, true);


--
-- Name: attendance_id_seq; Type: SEQUENCE SET; Schema: eis; Owner: eis_user
--

SELECT pg_catalog.setval('eis.attendance_id_seq', 1, false);


--
-- Name: departments_id_seq; Type: SEQUENCE SET; Schema: eis; Owner: eis_user
--

SELECT pg_catalog.setval('eis.departments_id_seq', 3, true);


--
-- Name: emergency_contacts_id_seq; Type: SEQUENCE SET; Schema: eis; Owner: eis_user
--

SELECT pg_catalog.setval('eis.emergency_contacts_id_seq', 1, false);


--
-- Name: employees_id_seq; Type: SEQUENCE SET; Schema: eis; Owner: eis_user
--

SELECT pg_catalog.setval('eis.employees_id_seq', 16, true);


--
-- Name: leave_requests_id_seq; Type: SEQUENCE SET; Schema: eis; Owner: eis_user
--

SELECT pg_catalog.setval('eis.leave_requests_id_seq', 1, false);


--
-- Name: leave_types_id_seq; Type: SEQUENCE SET; Schema: eis; Owner: eis_user
--

SELECT pg_catalog.setval('eis.leave_types_id_seq', 1, false);


--
-- Name: payroll_id_seq; Type: SEQUENCE SET; Schema: eis; Owner: eis_user
--

SELECT pg_catalog.setval('eis.payroll_id_seq', 1, false);


--
-- Name: positions_id_seq; Type: SEQUENCE SET; Schema: eis; Owner: eis_user
--

SELECT pg_catalog.setval('eis.positions_id_seq', 7, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: eis; Owner: eis_user
--

SELECT pg_catalog.setval('eis.users_id_seq', 5, true);


--
-- Name: categories_id_seq; Type: SEQUENCE SET; Schema: inventory; Owner: inventory_user
--

SELECT pg_catalog.setval('inventory.categories_id_seq', 5, true);


--
-- Name: products_id_seq; Type: SEQUENCE SET; Schema: inventory; Owner: inventory_user
--

SELECT pg_catalog.setval('inventory.products_id_seq', 9, true);


--
-- Name: purchase_order_items_id_seq; Type: SEQUENCE SET; Schema: inventory; Owner: inventory_user
--

SELECT pg_catalog.setval('inventory.purchase_order_items_id_seq', 2, true);


--
-- Name: purchase_orders_id_seq; Type: SEQUENCE SET; Schema: inventory; Owner: inventory_user
--

SELECT pg_catalog.setval('inventory.purchase_orders_id_seq', 1, true);


--
-- Name: stock_movements_id_seq; Type: SEQUENCE SET; Schema: inventory; Owner: inventory_user
--

SELECT pg_catalog.setval('inventory.stock_movements_id_seq', 5, true);


--
-- Name: suppliers_id_seq; Type: SEQUENCE SET; Schema: inventory; Owner: inventory_user
--

SELECT pg_catalog.setval('inventory.suppliers_id_seq', 5, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: inventory; Owner: inventory_user
--

SELECT pg_catalog.setval('inventory.users_id_seq', 6, true);


--
-- Name: menu_categories menu_categories_name_key; Type: CONSTRAINT; Schema: canteen; Owner: canteen_user
--

ALTER TABLE ONLY canteen.menu_categories
    ADD CONSTRAINT menu_categories_name_key UNIQUE (name);


--
-- Name: menu_categories menu_categories_pkey; Type: CONSTRAINT; Schema: canteen; Owner: canteen_user
--

ALTER TABLE ONLY canteen.menu_categories
    ADD CONSTRAINT menu_categories_pkey PRIMARY KEY (id);


--
-- Name: menu_items menu_items_pkey; Type: CONSTRAINT; Schema: canteen; Owner: canteen_user
--

ALTER TABLE ONLY canteen.menu_items
    ADD CONSTRAINT menu_items_pkey PRIMARY KEY (id);


--
-- Name: menu_items menu_items_product_id_key; Type: CONSTRAINT; Schema: canteen; Owner: canteen_user
--

ALTER TABLE ONLY canteen.menu_items
    ADD CONSTRAINT menu_items_product_id_key UNIQUE (product_id);


--
-- Name: payments payments_pkey; Type: CONSTRAINT; Schema: canteen; Owner: canteen_user
--

ALTER TABLE ONLY canteen.payments
    ADD CONSTRAINT payments_pkey PRIMARY KEY (id);


--
-- Name: payments payments_sale_id_key; Type: CONSTRAINT; Schema: canteen; Owner: canteen_user
--

ALTER TABLE ONLY canteen.payments
    ADD CONSTRAINT payments_sale_id_key UNIQUE (sale_id);


--
-- Name: sale_items sale_items_pkey; Type: CONSTRAINT; Schema: canteen; Owner: canteen_user
--

ALTER TABLE ONLY canteen.sale_items
    ADD CONSTRAINT sale_items_pkey PRIMARY KEY (id);


--
-- Name: sales sales_pkey; Type: CONSTRAINT; Schema: canteen; Owner: canteen_user
--

ALTER TABLE ONLY canteen.sales
    ADD CONSTRAINT sales_pkey PRIMARY KEY (id);


--
-- Name: sales sales_receipt_no_key; Type: CONSTRAINT; Schema: canteen; Owner: canteen_user
--

ALTER TABLE ONLY canteen.sales
    ADD CONSTRAINT sales_receipt_no_key UNIQUE (receipt_no);


--
-- Name: shifts shifts_pkey; Type: CONSTRAINT; Schema: canteen; Owner: canteen_user
--

ALTER TABLE ONLY canteen.shifts
    ADD CONSTRAINT shifts_pkey PRIMARY KEY (id);


--
-- Name: users users_employee_id_key; Type: CONSTRAINT; Schema: canteen; Owner: canteen_user
--

ALTER TABLE ONLY canteen.users
    ADD CONSTRAINT users_employee_id_key UNIQUE (employee_id);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: canteen; Owner: canteen_user
--

ALTER TABLE ONLY canteen.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: users users_username_key; Type: CONSTRAINT; Schema: canteen; Owner: canteen_user
--

ALTER TABLE ONLY canteen.users
    ADD CONSTRAINT users_username_key UNIQUE (username);


--
-- Name: attendance attendance_employee_id_work_date_key; Type: CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.attendance
    ADD CONSTRAINT attendance_employee_id_work_date_key UNIQUE (employee_id, work_date);


--
-- Name: attendance attendance_pkey; Type: CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.attendance
    ADD CONSTRAINT attendance_pkey PRIMARY KEY (id);


--
-- Name: departments departments_name_key; Type: CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.departments
    ADD CONSTRAINT departments_name_key UNIQUE (name);


--
-- Name: departments departments_pkey; Type: CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.departments
    ADD CONSTRAINT departments_pkey PRIMARY KEY (id);


--
-- Name: emergency_contacts emergency_contacts_pkey; Type: CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.emergency_contacts
    ADD CONSTRAINT emergency_contacts_pkey PRIMARY KEY (id);


--
-- Name: employees employees_email_key; Type: CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.employees
    ADD CONSTRAINT employees_email_key UNIQUE (email);


--
-- Name: employees employees_employee_no_key; Type: CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.employees
    ADD CONSTRAINT employees_employee_no_key UNIQUE (employee_no);


--
-- Name: employees employees_pkey; Type: CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.employees
    ADD CONSTRAINT employees_pkey PRIMARY KEY (id);


--
-- Name: leave_requests leave_requests_pkey; Type: CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.leave_requests
    ADD CONSTRAINT leave_requests_pkey PRIMARY KEY (id);


--
-- Name: leave_types leave_types_name_key; Type: CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.leave_types
    ADD CONSTRAINT leave_types_name_key UNIQUE (name);


--
-- Name: leave_types leave_types_pkey; Type: CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.leave_types
    ADD CONSTRAINT leave_types_pkey PRIMARY KEY (id);


--
-- Name: payroll payroll_employee_id_period_start_period_end_key; Type: CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.payroll
    ADD CONSTRAINT payroll_employee_id_period_start_period_end_key UNIQUE (employee_id, period_start, period_end);


--
-- Name: payroll payroll_pkey; Type: CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.payroll
    ADD CONSTRAINT payroll_pkey PRIMARY KEY (id);


--
-- Name: positions positions_department_id_title_key; Type: CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.positions
    ADD CONSTRAINT positions_department_id_title_key UNIQUE (department_id, title);


--
-- Name: positions positions_pkey; Type: CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.positions
    ADD CONSTRAINT positions_pkey PRIMARY KEY (id);


--
-- Name: users users_employee_id_key; Type: CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.users
    ADD CONSTRAINT users_employee_id_key UNIQUE (employee_id);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: users users_username_key; Type: CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.users
    ADD CONSTRAINT users_username_key UNIQUE (username);


--
-- Name: categories categories_name_key; Type: CONSTRAINT; Schema: inventory; Owner: inventory_user
--

ALTER TABLE ONLY inventory.categories
    ADD CONSTRAINT categories_name_key UNIQUE (name);


--
-- Name: categories categories_pkey; Type: CONSTRAINT; Schema: inventory; Owner: inventory_user
--

ALTER TABLE ONLY inventory.categories
    ADD CONSTRAINT categories_pkey PRIMARY KEY (id);


--
-- Name: products products_barcode_key; Type: CONSTRAINT; Schema: inventory; Owner: inventory_user
--

ALTER TABLE ONLY inventory.products
    ADD CONSTRAINT products_barcode_key UNIQUE (barcode);


--
-- Name: products products_pkey; Type: CONSTRAINT; Schema: inventory; Owner: inventory_user
--

ALTER TABLE ONLY inventory.products
    ADD CONSTRAINT products_pkey PRIMARY KEY (id);


--
-- Name: products products_sku_key; Type: CONSTRAINT; Schema: inventory; Owner: inventory_user
--

ALTER TABLE ONLY inventory.products
    ADD CONSTRAINT products_sku_key UNIQUE (sku);


--
-- Name: purchase_order_items purchase_order_items_pkey; Type: CONSTRAINT; Schema: inventory; Owner: inventory_user
--

ALTER TABLE ONLY inventory.purchase_order_items
    ADD CONSTRAINT purchase_order_items_pkey PRIMARY KEY (id);


--
-- Name: purchase_order_items purchase_order_items_purchase_order_id_product_id_key; Type: CONSTRAINT; Schema: inventory; Owner: inventory_user
--

ALTER TABLE ONLY inventory.purchase_order_items
    ADD CONSTRAINT purchase_order_items_purchase_order_id_product_id_key UNIQUE (purchase_order_id, product_id);


--
-- Name: purchase_orders purchase_orders_pkey; Type: CONSTRAINT; Schema: inventory; Owner: inventory_user
--

ALTER TABLE ONLY inventory.purchase_orders
    ADD CONSTRAINT purchase_orders_pkey PRIMARY KEY (id);


--
-- Name: purchase_orders purchase_orders_po_number_key; Type: CONSTRAINT; Schema: inventory; Owner: inventory_user
--

ALTER TABLE ONLY inventory.purchase_orders
    ADD CONSTRAINT purchase_orders_po_number_key UNIQUE (po_number);


--
-- Name: stock_movements stock_movements_pkey; Type: CONSTRAINT; Schema: inventory; Owner: inventory_user
--

ALTER TABLE ONLY inventory.stock_movements
    ADD CONSTRAINT stock_movements_pkey PRIMARY KEY (id);


--
-- Name: suppliers suppliers_name_key; Type: CONSTRAINT; Schema: inventory; Owner: inventory_user
--

ALTER TABLE ONLY inventory.suppliers
    ADD CONSTRAINT suppliers_name_key UNIQUE (name);


--
-- Name: suppliers suppliers_pkey; Type: CONSTRAINT; Schema: inventory; Owner: inventory_user
--

ALTER TABLE ONLY inventory.suppliers
    ADD CONSTRAINT suppliers_pkey PRIMARY KEY (id);


--
-- Name: users users_employee_id_key; Type: CONSTRAINT; Schema: inventory; Owner: inventory_user
--

ALTER TABLE ONLY inventory.users
    ADD CONSTRAINT users_employee_id_key UNIQUE (employee_id);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: inventory; Owner: inventory_user
--

ALTER TABLE ONLY inventory.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: users users_username_key; Type: CONSTRAINT; Schema: inventory; Owner: inventory_user
--

ALTER TABLE ONLY inventory.users
    ADD CONSTRAINT users_username_key UNIQUE (username);


--
-- Name: idx_sale_items_sale; Type: INDEX; Schema: canteen; Owner: canteen_user
--

CREATE INDEX idx_sale_items_sale ON canteen.sale_items USING btree (sale_id);


--
-- Name: idx_sales_cashier; Type: INDEX; Schema: canteen; Owner: canteen_user
--

CREATE INDEX idx_sales_cashier ON canteen.sales USING btree (cashier_id);


--
-- Name: idx_sales_sold_at; Type: INDEX; Schema: canteen; Owner: canteen_user
--

CREATE INDEX idx_sales_sold_at ON canteen.sales USING btree (sold_at);


--
-- Name: one_open_shift_per_cashier; Type: INDEX; Schema: canteen; Owner: canteen_user
--

CREATE UNIQUE INDEX one_open_shift_per_cashier ON canteen.shifts USING btree (cashier_id) WHERE ((status)::text = 'open'::text);


--
-- Name: idx_movements_product; Type: INDEX; Schema: inventory; Owner: inventory_user
--

CREATE INDEX idx_movements_product ON inventory.stock_movements USING btree (product_id, created_at);


--
-- Name: idx_products_category; Type: INDEX; Schema: inventory; Owner: inventory_user
--

CREATE INDEX idx_products_category ON inventory.products USING btree (category_id);


--
-- Name: menu_items menu_items_category_id_fkey; Type: FK CONSTRAINT; Schema: canteen; Owner: canteen_user
--

ALTER TABLE ONLY canteen.menu_items
    ADD CONSTRAINT menu_items_category_id_fkey FOREIGN KEY (category_id) REFERENCES canteen.menu_categories(id);


--
-- Name: menu_items menu_items_product_id_fkey; Type: FK CONSTRAINT; Schema: canteen; Owner: canteen_user
--

ALTER TABLE ONLY canteen.menu_items
    ADD CONSTRAINT menu_items_product_id_fkey FOREIGN KEY (product_id) REFERENCES inventory.products(id);


--
-- Name: payments payments_sale_id_fkey; Type: FK CONSTRAINT; Schema: canteen; Owner: canteen_user
--

ALTER TABLE ONLY canteen.payments
    ADD CONSTRAINT payments_sale_id_fkey FOREIGN KEY (sale_id) REFERENCES canteen.sales(id) ON DELETE CASCADE;


--
-- Name: sale_items sale_items_menu_item_id_fkey; Type: FK CONSTRAINT; Schema: canteen; Owner: canteen_user
--

ALTER TABLE ONLY canteen.sale_items
    ADD CONSTRAINT sale_items_menu_item_id_fkey FOREIGN KEY (menu_item_id) REFERENCES canteen.menu_items(id);


--
-- Name: sale_items sale_items_sale_id_fkey; Type: FK CONSTRAINT; Schema: canteen; Owner: canteen_user
--

ALTER TABLE ONLY canteen.sale_items
    ADD CONSTRAINT sale_items_sale_id_fkey FOREIGN KEY (sale_id) REFERENCES canteen.sales(id) ON DELETE CASCADE;


--
-- Name: sales sales_cashier_id_fkey; Type: FK CONSTRAINT; Schema: canteen; Owner: canteen_user
--

ALTER TABLE ONLY canteen.sales
    ADD CONSTRAINT sales_cashier_id_fkey FOREIGN KEY (cashier_id) REFERENCES eis.employees(id);


--
-- Name: sales sales_shift_id_fkey; Type: FK CONSTRAINT; Schema: canteen; Owner: canteen_user
--

ALTER TABLE ONLY canteen.sales
    ADD CONSTRAINT sales_shift_id_fkey FOREIGN KEY (shift_id) REFERENCES canteen.shifts(id);


--
-- Name: sales sales_voided_by_fkey; Type: FK CONSTRAINT; Schema: canteen; Owner: canteen_user
--

ALTER TABLE ONLY canteen.sales
    ADD CONSTRAINT sales_voided_by_fkey FOREIGN KEY (voided_by) REFERENCES eis.employees(id);


--
-- Name: shifts shifts_cashier_id_fkey; Type: FK CONSTRAINT; Schema: canteen; Owner: canteen_user
--

ALTER TABLE ONLY canteen.shifts
    ADD CONSTRAINT shifts_cashier_id_fkey FOREIGN KEY (cashier_id) REFERENCES eis.employees(id);


--
-- Name: users users_employee_id_fkey; Type: FK CONSTRAINT; Schema: canteen; Owner: canteen_user
--

ALTER TABLE ONLY canteen.users
    ADD CONSTRAINT users_employee_id_fkey FOREIGN KEY (employee_id) REFERENCES eis.employees(id);


--
-- Name: attendance attendance_employee_id_fkey; Type: FK CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.attendance
    ADD CONSTRAINT attendance_employee_id_fkey FOREIGN KEY (employee_id) REFERENCES eis.employees(id);


--
-- Name: emergency_contacts emergency_contacts_employee_id_fkey; Type: FK CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.emergency_contacts
    ADD CONSTRAINT emergency_contacts_employee_id_fkey FOREIGN KEY (employee_id) REFERENCES eis.employees(id) ON DELETE CASCADE;


--
-- Name: employees employees_department_id_fkey; Type: FK CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.employees
    ADD CONSTRAINT employees_department_id_fkey FOREIGN KEY (department_id) REFERENCES eis.departments(id);


--
-- Name: employees employees_position_id_fkey; Type: FK CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.employees
    ADD CONSTRAINT employees_position_id_fkey FOREIGN KEY (position_id) REFERENCES eis.positions(id);


--
-- Name: leave_requests leave_requests_approved_by_fkey; Type: FK CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.leave_requests
    ADD CONSTRAINT leave_requests_approved_by_fkey FOREIGN KEY (approved_by) REFERENCES eis.employees(id);


--
-- Name: leave_requests leave_requests_employee_id_fkey; Type: FK CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.leave_requests
    ADD CONSTRAINT leave_requests_employee_id_fkey FOREIGN KEY (employee_id) REFERENCES eis.employees(id);


--
-- Name: leave_requests leave_requests_leave_type_id_fkey; Type: FK CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.leave_requests
    ADD CONSTRAINT leave_requests_leave_type_id_fkey FOREIGN KEY (leave_type_id) REFERENCES eis.leave_types(id);


--
-- Name: payroll payroll_employee_id_fkey; Type: FK CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.payroll
    ADD CONSTRAINT payroll_employee_id_fkey FOREIGN KEY (employee_id) REFERENCES eis.employees(id);


--
-- Name: positions positions_department_id_fkey; Type: FK CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.positions
    ADD CONSTRAINT positions_department_id_fkey FOREIGN KEY (department_id) REFERENCES eis.departments(id);


--
-- Name: users users_employee_id_fkey; Type: FK CONSTRAINT; Schema: eis; Owner: eis_user
--

ALTER TABLE ONLY eis.users
    ADD CONSTRAINT users_employee_id_fkey FOREIGN KEY (employee_id) REFERENCES eis.employees(id);


--
-- Name: products products_category_id_fkey; Type: FK CONSTRAINT; Schema: inventory; Owner: inventory_user
--

ALTER TABLE ONLY inventory.products
    ADD CONSTRAINT products_category_id_fkey FOREIGN KEY (category_id) REFERENCES inventory.categories(id);


--
-- Name: products products_supplier_id_fkey; Type: FK CONSTRAINT; Schema: inventory; Owner: inventory_user
--

ALTER TABLE ONLY inventory.products
    ADD CONSTRAINT products_supplier_id_fkey FOREIGN KEY (supplier_id) REFERENCES inventory.suppliers(id);


--
-- Name: purchase_order_items purchase_order_items_product_id_fkey; Type: FK CONSTRAINT; Schema: inventory; Owner: inventory_user
--

ALTER TABLE ONLY inventory.purchase_order_items
    ADD CONSTRAINT purchase_order_items_product_id_fkey FOREIGN KEY (product_id) REFERENCES inventory.products(id);


--
-- Name: purchase_order_items purchase_order_items_purchase_order_id_fkey; Type: FK CONSTRAINT; Schema: inventory; Owner: inventory_user
--

ALTER TABLE ONLY inventory.purchase_order_items
    ADD CONSTRAINT purchase_order_items_purchase_order_id_fkey FOREIGN KEY (purchase_order_id) REFERENCES inventory.purchase_orders(id) ON DELETE CASCADE;


--
-- Name: purchase_orders purchase_orders_created_by_fkey; Type: FK CONSTRAINT; Schema: inventory; Owner: inventory_user
--

ALTER TABLE ONLY inventory.purchase_orders
    ADD CONSTRAINT purchase_orders_created_by_fkey FOREIGN KEY (created_by) REFERENCES inventory.users(id);


--
-- Name: purchase_orders purchase_orders_supplier_id_fkey; Type: FK CONSTRAINT; Schema: inventory; Owner: inventory_user
--

ALTER TABLE ONLY inventory.purchase_orders
    ADD CONSTRAINT purchase_orders_supplier_id_fkey FOREIGN KEY (supplier_id) REFERENCES inventory.suppliers(id);


--
-- Name: stock_movements stock_movements_performed_by_fkey; Type: FK CONSTRAINT; Schema: inventory; Owner: inventory_user
--

ALTER TABLE ONLY inventory.stock_movements
    ADD CONSTRAINT stock_movements_performed_by_fkey FOREIGN KEY (performed_by) REFERENCES inventory.users(id);


--
-- Name: stock_movements stock_movements_product_id_fkey; Type: FK CONSTRAINT; Schema: inventory; Owner: inventory_user
--

ALTER TABLE ONLY inventory.stock_movements
    ADD CONSTRAINT stock_movements_product_id_fkey FOREIGN KEY (product_id) REFERENCES inventory.products(id);


--
-- Name: users users_employee_id_fkey; Type: FK CONSTRAINT; Schema: inventory; Owner: inventory_user
--

ALTER TABLE ONLY inventory.users
    ADD CONSTRAINT users_employee_id_fkey FOREIGN KEY (employee_id) REFERENCES eis.employees(id);


--
-- Name: SCHEMA eis; Type: ACL; Schema: -; Owner: eis_user
--

GRANT USAGE ON SCHEMA eis TO canteen_user;
GRANT USAGE ON SCHEMA eis TO inventory_user;


--
-- Name: SCHEMA inventory; Type: ACL; Schema: -; Owner: inventory_user
--

GRANT USAGE ON SCHEMA inventory TO canteen_user;


--
-- Name: FUNCTION canteen_stock_change(p_product_id bigint, p_change integer, p_sale_id bigint, p_reason text); Type: ACL; Schema: inventory; Owner: inventory_user
--

REVOKE ALL ON FUNCTION inventory.canteen_stock_change(p_product_id bigint, p_change integer, p_sale_id bigint, p_reason text) FROM PUBLIC;
GRANT ALL ON FUNCTION inventory.canteen_stock_change(p_product_id bigint, p_change integer, p_sale_id bigint, p_reason text) TO canteen_user;


--
-- Name: TABLE attendance; Type: ACL; Schema: eis; Owner: eis_user
--

GRANT SELECT,REFERENCES ON TABLE eis.attendance TO canteen_user;
GRANT SELECT,REFERENCES ON TABLE eis.attendance TO inventory_user;


--
-- Name: TABLE departments; Type: ACL; Schema: eis; Owner: eis_user
--

GRANT SELECT,REFERENCES ON TABLE eis.departments TO canteen_user;
GRANT SELECT,REFERENCES ON TABLE eis.departments TO inventory_user;


--
-- Name: TABLE emergency_contacts; Type: ACL; Schema: eis; Owner: eis_user
--

GRANT SELECT,REFERENCES ON TABLE eis.emergency_contacts TO canteen_user;
GRANT SELECT,REFERENCES ON TABLE eis.emergency_contacts TO inventory_user;


--
-- Name: TABLE employees; Type: ACL; Schema: eis; Owner: eis_user
--

GRANT SELECT,REFERENCES ON TABLE eis.employees TO canteen_user;
GRANT SELECT,REFERENCES ON TABLE eis.employees TO inventory_user;


--
-- Name: TABLE leave_requests; Type: ACL; Schema: eis; Owner: eis_user
--

GRANT SELECT,REFERENCES ON TABLE eis.leave_requests TO canteen_user;
GRANT SELECT,REFERENCES ON TABLE eis.leave_requests TO inventory_user;


--
-- Name: TABLE leave_types; Type: ACL; Schema: eis; Owner: eis_user
--

GRANT SELECT,REFERENCES ON TABLE eis.leave_types TO canteen_user;
GRANT SELECT,REFERENCES ON TABLE eis.leave_types TO inventory_user;


--
-- Name: TABLE payroll; Type: ACL; Schema: eis; Owner: eis_user
--

GRANT SELECT,REFERENCES ON TABLE eis.payroll TO canteen_user;
GRANT SELECT,REFERENCES ON TABLE eis.payroll TO inventory_user;


--
-- Name: TABLE positions; Type: ACL; Schema: eis; Owner: eis_user
--

GRANT SELECT,REFERENCES ON TABLE eis.positions TO canteen_user;
GRANT SELECT,REFERENCES ON TABLE eis.positions TO inventory_user;


--
-- Name: TABLE users; Type: ACL; Schema: eis; Owner: eis_user
--

GRANT SELECT,REFERENCES ON TABLE eis.users TO canteen_user;
GRANT SELECT,REFERENCES ON TABLE eis.users TO inventory_user;


--
-- Name: TABLE categories; Type: ACL; Schema: inventory; Owner: inventory_user
--

GRANT SELECT,REFERENCES ON TABLE inventory.categories TO canteen_user;


--
-- Name: TABLE products; Type: ACL; Schema: inventory; Owner: inventory_user
--

GRANT SELECT,REFERENCES ON TABLE inventory.products TO canteen_user;


--
-- Name: TABLE purchase_order_items; Type: ACL; Schema: inventory; Owner: inventory_user
--

GRANT SELECT,REFERENCES ON TABLE inventory.purchase_order_items TO canteen_user;


--
-- Name: TABLE purchase_orders; Type: ACL; Schema: inventory; Owner: inventory_user
--

GRANT SELECT,REFERENCES ON TABLE inventory.purchase_orders TO canteen_user;


--
-- Name: TABLE stock_movements; Type: ACL; Schema: inventory; Owner: inventory_user
--

GRANT SELECT,REFERENCES ON TABLE inventory.stock_movements TO canteen_user;


--
-- Name: TABLE suppliers; Type: ACL; Schema: inventory; Owner: inventory_user
--

GRANT SELECT,REFERENCES ON TABLE inventory.suppliers TO canteen_user;


--
-- Name: TABLE users; Type: ACL; Schema: inventory; Owner: inventory_user
--

GRANT SELECT,REFERENCES ON TABLE inventory.users TO canteen_user;


--
-- Name: DEFAULT PRIVILEGES FOR TABLES; Type: DEFAULT ACL; Schema: eis; Owner: eis_user
--

ALTER DEFAULT PRIVILEGES FOR ROLE eis_user IN SCHEMA eis GRANT SELECT,REFERENCES ON TABLES TO inventory_user;
ALTER DEFAULT PRIVILEGES FOR ROLE eis_user IN SCHEMA eis GRANT SELECT,REFERENCES ON TABLES TO canteen_user;


--
-- Name: DEFAULT PRIVILEGES FOR TABLES; Type: DEFAULT ACL; Schema: inventory; Owner: inventory_user
--

ALTER DEFAULT PRIVILEGES FOR ROLE inventory_user IN SCHEMA inventory GRANT SELECT,REFERENCES ON TABLES TO canteen_user;


--
-- PostgreSQL database dump complete
--

\unrestrict 3x1DJdXZqqW6fQenB72JcABeQSF0PcoUkAQ5coo2vM6eKTfaOCHnhpfhGoqd7gg

