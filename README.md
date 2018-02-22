# Satispay Magento Plugin

## How to release an update
- Change version in `src/app/code/community/Satispay/Satispay/etc/config.xml`
- Generate new `src/package.xml`
- Run `./scripts/bundle.sh`
- Rename `.tmp/Satispay_Satispay-x.x.x.tgz` like `.tmp/Satispay_Satispay-1.4.3.tgz`
- Upload to Magento Marketplace
