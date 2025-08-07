# Fakturowo.pl API Documentation

## Overview
- **Base URL**: `https://www.fakturowo.pl/api`
- **Authentication**: API key based (api_id)
- **Methods**: POST (recommended for production) and GET (for testing only)
- **Encoding**: UTF-8 (default)

## Authentication
Every API request requires the following parameters:
- `api_id`: Your unique API key (obtain from Fakturowo.pl account settings)
- `api_zadanie`: Operation type (1-8)
- `api_kodowanie`: Encoding type (optional, defaults to UTF-8)

## API Operations (api_zadanie)

### 1. Create Document (api_zadanie=1)
Creates a new invoice or other document.

**Required Parameters**:
- `api_id`: API key
- `api_zadanie`: "1"
- `dokument_typ`: Document type (e.g., "Faktura", "FakturaKorygujaca", "Rachunek")
- `dokument_miejsce`: Place of issue
- `sprzedawca_nazwa`: Seller name
- `sprzedawca_nip`: Seller tax ID
- `nabywca_nazwa`: Buyer name
- Product details (nazwa, cena_netto, vat, ilosc, etc.)

### 2. Delete Document (api_zadanie=2)
Deletes an existing document.

**Parameters**:
- `api_id`: API key
- `api_zadanie`: "2"
- `dokument_id`: Document ID to delete

### 3. List Documents (api_zadanie=3)
Retrieves a list of documents.

**Parameters**:
- `api_id`: API key
- `api_zadanie`: "3"
- Optional filters (date range, type, status, etc.)

### 4. List Clients (api_zadanie=4)
Retrieves a list of clients.

### 5. Delete Client (api_zadanie=5)
Deletes a client from the database.

### 6. List Products (api_zadanie=6)
Retrieves a list of products.

### 7. Delete Product (api_zadanie=7)
Deletes a product from the database.

### 8. Get Next Document Number (api_zadanie=8)
Retrieves the next available document number for a specific type.

## Sample Code

### GET Request (Testing Only)
```
https://www.fakturowo.pl/api?api_id=YOUR_API_KEY&api_zadanie=1&dokument_miejsce=Katowice&sprzedawca_nazwa=Your%20Company&...
```

### POST Request (Recommended)
```php
$api_params = array(
    "api_id" => "YOUR_API_KEY",
    "api_zadanie" => "1",
    "dokument_typ" => "Faktura",
    "dokument_miejsce" => "Katowice",
    "sprzedawca_nazwa" => "Your Company Name",
    "sprzedawca_nip" => "1234567890",
    "sprzedawca_adres" => "ul. Example 123",
    "sprzedawca_kod" => "00-000",
    "sprzedawca_miasto" => "Warsaw",
    "nabywca_nazwa" => "Client Company",
    "nabywca_nip" => "0987654321",
    "produkt_nazwa[0]" => "Product 1",
    "produkt_cena_netto[0]" => "100.00",
    "produkt_vat[0]" => "23",
    "produkt_ilosc[0]" => "1",
    "produkt_jednostka[0]" => "szt"
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://www.fakturowo.pl/api");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($api_params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);
```

## Important Notes
1. **Security**: Always use POST method in production environments
2. **Synchronization**: API calls should be made synchronously to avoid conflicts
3. **Error Handling**: Check response for errors before processing
4. **Rate Limiting**: Be mindful of API rate limits (not specified in documentation)

## Response Format
The API returns responses in JSON format with status and data fields.

## Source
Documentation retrieved from: https://www.fakturowo.pl/pomoc/api-podstawowe-funkcje