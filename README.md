# PhonePe-WHMCS-Payment-Gateway-Module
Download the PhonePe WHMCS integration module to accept UPI and PhonePe payments on your hosting business. Easy setup with staging &amp; production support. Free download available.
# PhonePe Payment Gateway for WHMCS

> Accept PhonePe and UPI payments directly inside your WHMCS billing panel — completely free, no monthly fees, ever.

![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue?logo=php)
![WHMCS](https://img.shields.io/badge/WHMCS-7.x%20%2F%208.x-orange)
![PhonePe API](https://img.shields.io/badge/PhonePe%20PG-API%20v1-purple)
![License](https://img.shields.io/badge/License-Free%20%26%20Open%20Source-green)
![Version](https://img.shields.io/badge/Version-1.0-lightgrey)

---

## 📖 Overview

This integration kit connects **PhonePe's Payment Gateway** with your **WHMCS billing system**. Whether you sell web hosting, domain names, or any digital services through WHMCS, this module lets your customers pay directly via PhonePe, UPI QR, or any UPI app — with zero subscription or hidden cost.

---

## ✨ Features

| Feature | Description |
|---|---|
| 🚀 **3-Step Install** | Copy 3 files. No coding knowledge required. |
| 🧪 **Sandbox Testing** | Test safely in staging mode before going live — no real money involved. |
| ✅ **Auto Callback** | WHMCS automatically marks invoices as paid once payment is confirmed. |
| 🔐 **Salt Key Security** | Uses PhonePe's official Salt Key + Salt Index authentication. |
| 📱 **UPI + PhonePe** | Customers can pay via UPI ID, QR code, or directly from the PhonePe app. |
| 💸 **Zero Cost** | 100% free. No license fee, no monthly charges, no hidden costs. |

---

## 🛠️ Requirements

- PHP **7.4** or above
- WHMCS **7.x** or **8.x**
- PhonePe Business account (for production credentials)

---

## 📁 Folder Structure

```
phonepe-whmcs/
├── gateways/
│   ├── phonepe.php              # Main gateway file
│   └── callback/
│       └── phonepe.php          # Payment callback handler
├── phonepe/                     # Core module files
│   └── (module files)
└── phonepe-sdk/                 # PhonePe official PHP SDK
    └── (SDK files)
```

---

## 📦 Installation

### Step 1 — Copy the Gateway File
Copy `gateways/phonepe.php` into your WHMCS installation's `/modules/gateways/` folder.

### Step 2 — Copy the Callback File
Copy `gateways/callback/phonepe.php` into `/modules/gateways/callback/`.  
This file handles payment confirmations from PhonePe.

### Step 3 — Copy the PhonePe Module Folder
Copy the entire `phonepe/` folder into your WHMCS `/modules/gateways/` directory.  
This contains the core module files required for PhonePe to function.

### Step 4 — Copy the SDK Folder
Copy the entire `phonepe-sdk/` folder into `/modules/gateways/`.  
This is PhonePe's official PHP SDK.

### Step 5 — Configure in WHMCS Admin Panel
Go to:
> **WHMCS Admin Panel → Setup → Payment Gateways → Activate PhonePe**

Fill in your **Merchant ID**, **Salt Key**, **Salt Index**, and **Production URL**.

---

## ⚙️ Configuration

### Staging (Sandbox) Settings

| Field | Value |
|---|---|
| Production URL | `https://api-preprod.phonepe.com/apis/pg-sandbox/pg/v1` |
| Merchant ID | `PGTESTPAYUAT` |
| Salt Key | `099eb0cd-02cf-4e2a-8aca-3e6c6aff0399` |
| Salt Index | `1` |

### Production Settings

| Field | Value |
|---|---|
| Production URL | `https://api.phonepe.com/apis/hermes/pg/v1` |
| Merchant ID | From your PhonePe Business Dashboard |
| Salt Key | From your PhonePe Business Dashboard |
| Salt Index | From your PhonePe Business Dashboard |

> ⚠️ **Note:** Staging mode does **not** process real transactions. Switch to production credentials only after successful testing.

---

## ❓ FAQ

**Is this module completely free?**  
Yes. This module is 100% free and open source — no subscription, no license fee, no hidden charges.

**Where do I get Production Merchant ID and Salt Key?**  
Register at [business.phonepe.com](https://business.phonepe.com). After approval, your credentials will be available under **API Credentials** in the PhonePe Business Dashboard.

**What happens if a payment fails?**  
The callback handler notifies WHMCS automatically. The invoice remains unpaid and the customer can retry the payment.

**Which WHMCS versions are supported?**  
WHMCS 7.x and 8.x with PHP 7.4 or above.

---

## 🔗 Resources

- 📄 [Full Installation Guide](https://skyserver.in/blogs/post/phonepe-whmcs-payment-gateway-module-free-download)
- 🏢 [PhonePe Business Dashboard](https://business.phonepe.com)
- 📞 [WHMCS Documentation](https://developers.whmcs.com/payment-gateways/)

---

## 🤝 Credits

Module developed and maintained by **[SkyServer Cloud Technologies](https://skyserver.in)**.  
Office: BT-1, Bio Technology Park, Sitapura, Jaipur — 302022  
Support: support@skyserver.in

---

## 📜 License

This project is free and open source. You are free to use, modify, and distribute it.

---

> ⭐ If this module helped you, please consider giving the repo a star!
