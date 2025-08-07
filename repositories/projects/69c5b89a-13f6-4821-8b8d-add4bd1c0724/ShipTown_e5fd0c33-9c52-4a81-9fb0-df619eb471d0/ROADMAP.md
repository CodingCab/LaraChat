# ShipTown Roadmap
This document outlines the development roadmap for ShipTown, an inventory management system designed for warehouses, retail, and ecommerce operations.

## Table of Contents

- [General Info](#general-info)
- [Success Metrics](#success-metrics)
- [Urgent Tasks](#urgent-tasks)
  - [P&L Account Reporting Requirements](#pl-account-reporting-requirements)
- [Tasks](#tasks)
  - [Current Tasks](#current-tasks)
  - [Weekly Tasks](#weekly-tasks)
  - [Code Improvements and Fixes](#code-improvements-and-fixes)
  - [Shopify Integration](#shopify-integration)
  - [Suppliers Management Feature](#suppliers-management-feature)
- [Short Term Goals (Q3-Q4 2024)](#short-term-goals-q3-q4-2024)
  - [Framework](#framework)
  - [Performance Optimization](#performance-optimization)
  - [User Experience](#user-experience)
- [Medium Term Goals (Q1-Q2 2025)](#medium-term-goals-q1-q2-2025)
  - [Analytics & Reporting](#analytics--reporting)
  - [Automation](#automation)
  - [Multi-tenant Features](#multi-tenant-features)
- [Long Term Vision (2025+)](#long-term-vision-2025)
  - [AI/ML Integration](#aiml-integration)
  - [Global Expansion](#global-expansion)
  - [Enterprise Features](#enterprise-features)
- [Technical Debt & Infrastructure](#technical-debt--infrastructure)
  - [Ongoing Improvements](#ongoing-improvements)
  - [Security Enhancements](#security-enhancements)
- [Community & Ecosystem](#community--ecosystem)
  - [Developer Experience](#developer-experience)
  - [Open Source Initiatives](#open-source-initiatives)
- [Notes](#notes)

#### General Info
- Order management and fulfillment
- Multi-warehouse, multi-location support
- API integrations (Magento2, API2Cart)
- Connected couriers: DPD Ireland, DPD UK, DPD Poland (via ShippyPro), InPost Poland (via ShippyPro), Scurri/An Post
- Laravel 11 based inventory management system
- Modular architecture with installable modules

#### Success Metrics
- [x] ~~Support 3 locations~~
- [ ] Support 20 locations
- [ ] Support 100 locations
- [x] ~~Handle 100k+ orders/year efficiently~~
- [ ] Handle 500k orders/year efficiently
- [ ] Handle 10 mln orders/year efficiently
- [ ] Maintain under 1s response time for every endpoint
- [ ] Maintain under 500ms response time for every endpoint
- [ ] Maintain under 200ms response time for every endpoint
- [ ] 99.9% uptime SLA

## Urgent Tasks
### P&L Account Reporting Requirements
- [ ] Summary report for transfer of stock from warehouse to each shop by shop and department
  - [ ] List computed total columns from data collections records table which use quantity_scanned in their calculations
  - [ ] Recreate columns with total_quantity_adjusted instead of quantity_scanned
- [ ] Summary report for transfers of stock from shop to shop by individual shop and department
- [x] ~~Reports needed for year ended 31 Jan 2025 for auditors~~

## Tasks
---
### Current Tasks
- [ ] Sync Magento 2 staging for Korallo:
  - [ ] Import products to korallo-test.myshiptown.com
  - [ ] Attach available tags
  - [ ] Import stock
  - [ ] Import prices
  - [ ] Ensure all syncs correctly

### Weekly Tasks
- [ ] #101 | Remove unnecessary PHPDoc blocks: Remove PHPDoc blocks that only contain `@return void` - max 20 files at a time
- [ ] #102 | Run composer update and fix all post update issues

### Code Improvements and Fixes
- [ ] Fix PHPUnit Deprecation Warnings - update metadata in doc-comments to use attributes:
  - [ ] Tests\Jobs\UpdateInventoryMovementProductDetailsJobTest
  - [ ] Tests\Unit\Modules\Fakturowo\RaiseFakturowoPLInvoiceExceptionTest
- [ ] Add Missing OrderStatus Seeder - ensure 'new' status exists in database
- [ ] Fix OrderStatusFactory - include 'new' in available statuses list

### Shopify Integration
- [ ] Get first test client for Shopify integration
- [ ] Create new module: `app/Modules/ShopifyApi/`
- [ ] Design and implement database structure:
  - [ ] Create modules_shopify_connections table (shop domain, access token, webhook url, etc.)
  - [ ] Create modules_shopify_products_mapping table (map Shopify products to ShipTown products)
  - [ ] Create modules_shopify_orders table (track imported orders)
- [ ] Implement OAuth2 authentication flow:
  - [ ] Create Shopify app installation route
  - [ ] Handle OAuth callback and token storage
  - [ ] Implement HMAC verification for security
- [ ] Build Shopify API client service:
  - [ ] REST API wrapper with authentication
  - [ ] Rate limiting and retry logic
  - [ ] Error handling and logging
- [ ] Implement order synchronization:
  - [ ] Create SyncShopifyOrdersJob (run every 5 minutes)
  - [ ] Map Shopify order format to ShipTown format
  - [ ] Store original Shopify order data for reference
  - [ ] Register webhooks for real-time order updates
  - [ ] Update order status back to Shopify after fulfillment
- [ ] Implement inventory synchronization:
  - [ ] Create SyncInventoryToShopifyJob (run every 15 minutes)
  - [ ] Listen to InventoryUpdated events
  - [ ] Push stock levels to Shopify
  - [ ] Support multi-location inventory
  - [ ] Handle batch updates and failures
- [ ] Implement pricing synchronization:
  - [ ] Create SyncPricingToShopifyJob (run hourly)
  - [ ] Listen to ProductPriceUpdated events
  - [ ] Push pricing updates to Shopify
  - [ ] Support multi-currency if needed
- [ ] Create data transformers:
  - [ ] ShopifyOrderTransformer (Shopify → ShipTown)
  - [ ] OrderFulfillmentTransformer (ShipTown → Shopify)
  - [ ] InventoryLevelTransformer
  - [ ] ProductPriceTransformer
- [ ] Implement artisan commands:
  - [ ] `shopify:install {shop_domain}` - Install app for shop
  - [ ] `shopify:sync-orders {connection_id?}` - Manual order sync
  - [ ] `shopify:sync-inventory {connection_id?}` - Manual inventory sync
  - [ ] `shopify:sync-pricing {connection_id?}` - Manual pricing sync
- [ ] Build user interface:
  - [ ] Shopify connection management page
  - [ ] Connection status dashboard
  - [ ] Manual sync triggers
  - [ ] Error logs viewer
  - [ ] Product mapping interface with bulk tools
- [ ] Comprehensive testing:
  - [ ] Unit tests for transformers and API client
  - [ ] Feature tests for OAuth flow and sync processes
  - [ ] Integration tests with Shopify test store
  - [ ] Mock Shopify API responses for testing
- [ ] Documentation and deployment:
  - [ ] Write integration guide
  - [ ] Document API endpoints and webhooks
  - [ ] Deploy to test client
  - [ ] Monitor and refine based on usage

### Suppliers Management Feature
- [ ] Create database structure for suppliers:
  - [ ] Create suppliers table (id, name, code, address_id, timestamps)
  - [ ] Create supplier_products junction table (supplier_id, product_id, product_number, carton_quantity, currency_id, cost)
  - [ ] Create currencies table if multi-currency support needed (id, name, currency_code)
  - [ ] Add database migrations for all new tables
  - [ ] Create unique index on supplier_id + product_id in supplier_products
- [ ] Implement Supplier model and relationships:
  - [ ] Supplier model with hasMany supplier_products
  - [ ] Update Product model with hasMany supplier_products
  - [ ] SupplierProduct model with belongsTo relationships
- [ ] Build supplier management functionality:
  - [ ] Create, edit, and delete suppliers API endpoints
  - [ ] Implement supplier search functionality
  - [ ] Add validation for supplier code uniqueness
- [ ] Implement product-supplier assignment:
  - [ ] Create UI for linking products to suppliers
  - [ ] Build form for cost, currency, product number, carton quantity
  - [ ] Support assigning multiple suppliers to one product
  - [ ] Bulk assignment tools
- [ ] Add multi-currency support:
  - [ ] Implement currency management
  - [ ] Add currency selection in supplier product forms
  - [ ] Consider exchange rate conversion mechanism
  - [ ] Store costs in supplier's currency
- [ ] Create reporting features:
  - [ ] List suppliers for a given product
  - [ ] Filter by currency, cost range, supplier code
  - [ ] Export supplier data to CSV
  - [ ] API endpoint for supplier queries
- [ ] Build import functionality:
  - [ ] CSV import for suppliers
  - [ ] CSV import for supplier-product mappings
  - [ ] Validation and error reporting
- [ ] Develop user interface:
  - [ ] Supplier management page in admin panel
  - [ ] Product-supplier assignment interface
  - [ ] Bulk editing capabilities
  - [ ] Search and filter options
- [ ] Handle edge cases:
  - [ ] Cascade delete or safeguards for supplier/product deletion
  - [ ] Currency conversion for reports
  - [ ] Performance optimization with proper indexing
- [ ] Optional features:
  - [ ] Default/preferred supplier per product
  - [ ] Price change history tracking
  - [ ] Supplier discount management
  - [ ] Price change notifications
  - [ ] ERP integration capabilities

## Short Term Goals (Q3-Q4 2024)

### Framework
- [ ] Upgrade to Vue 3 (before Oct)
- [ ] Upgrade to Laravel 12 (before Jan)

### Performance Optimization
- [ ] Optimize database queries for 10M+ inventory records
- [ ] Implement caching strategies for frequently accessed data
- [ ] Improve CSV import performance for large datasets

### User Experience
- [ ] Enhanced mobile responsiveness for warehouse floor usage
- [ ] Improved barcode scanning workflows
- [ ] Streamlined picking and packing interface

## Medium Term Goals (Q1-Q2 2025)

### Analytics & Reporting
- [ ] Advanced inventory analytics dashboard
- [ ] Predictive stock level recommendations
- [ ] Custom report builder for business metrics

### Automation
- [ ] Expanded automation conditions and actions
- [ ] Workflow templates for common business processes
- [ ] Smart order routing based on inventory levels

### Multi-tenant Features
- [ ] Enhanced 3PL client management
- [ ] Client-specific branding and customization
- [ ] Isolated data access controls

## Long Term Vision (2025+)

### AI/ML Integration
- [ ] Demand forecasting based on historical data
- [ ] Automated reorder point calculations
- [ ] Anomaly detection for inventory discrepancies

### Global Expansion
- [ ] Multi-currency transaction support
- [ ] International shipping carrier integrations
- [ ] Compliance with regional inventory regulations

### Enterprise Features
- [ ] Advanced user permission systems
- [ ] Audit trail and compliance reporting
- [ ] Enterprise SSO integration

## Technical Debt & Infrastructure

### Ongoing Improvements
- [ ] Migrate remaining non-modular code to modules
- [ ] Comprehensive API documentation
- [ ] Expand test coverage to 90%+
- [ ] Performance monitoring and alerting

### Security Enhancements
- [ ] Regular security audits
- [ ] Enhanced API authentication options
- [ ] Data encryption at rest

## Community & Ecosystem

### Developer Experience
- [ ] Plugin/extension marketplace
- [ ] Developer documentation and tutorials
- [ ] API SDK for popular languages

### Open Source Initiatives
- [ ] Community contribution guidelines
- [ ] Public roadmap voting system
- [ ] Regular community calls

## Notes
This roadmap is subject to change based on user feedback and business priorities. We welcome community input and contributions to help shape the future of ShipTown.
