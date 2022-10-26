#!/usr/bin/env bash
aws configure set region us-east-2
chmod 775 /var/www/html/vendor/dompdf/dompdf/lib/fonts
service apache2 restart
