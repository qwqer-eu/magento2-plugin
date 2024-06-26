
# Magento 2 QWQER Express Delivery - Extension

##### For Magento / Adobe Commerce 2.4.x

### How to QWQER Express Delivery Extension

##### Install the extension using a zipped file

    Download the QWQER Express extension
    Unzip the file
    In your Magento 2 root directory, create the folder app/code/Qwqer/Express
    Move the extensions unzipped files and folders to the new folder in your root directory
    In the command line (using cd) navigate to your Magento 2 root directory
    Run the following commands in order:

```sh
php bin/magento module:enable Qwqer_Express
php bin/magento setup:upgrade
php bin/magento setup:di:compile
```

Your extension is now installed and accessible from the left navigation menu in your Magento 2 back office: Stores > Configuration > Sales > Delivery Methods > QWQER Express.

After successfully placed order, you can print shipping label in admin panel during view order information. Click button Print Label in Toolbar.

QWQER Order id saved in database table "sales_order"

### Main Configurations

Need setup store owner information:
* Stores -> General -> Store Email Addresses -> General Contact -> Sender Email
* Stores -> General -> Store Information -> Store Name
* Stores -> General -> Store Information -> Store Phone Number

QWQER delivery is available for products only:
- "Available Qwqer Delivery" product attribute value is "Yes"

#### QWQER Express

* Api Key - Get it in QWQER Express support
* Trading point id - Get it in QWQER Express support 
* Store Address - Base Store Address
* Store Geo Locations - uploaded automatically after setup Store Address
* Default Shipping Cost - Default shipping cost
* Sync order to QWQER automatically - sync manually or automatically after place order  

#### QWQER Scheduled to parcel locker

* Title
* Method Name
* Default Shipping Cost - Default shipping cost

#### QWQER Scheduled to door

* Title
* Method Name
* Default Parcel Size
* Default Shipping Cost - Default shipping cost

QWQER | [https://qwqer.lv/](https://qwqer.lv/)
