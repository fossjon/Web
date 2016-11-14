<?php
/*


vim /etc/hostname
domain-name.com


usermod -aG sasl postfix
postmap /etc/postfix/access.list


vim /etc/postfix/master.cf
smtp inet n - n - - smtpd


vim /etc/postfix/main.cf
myorigin = $mydomain
mydestination = $myhostname localhost.$mydomain localhost $mydomain
mynetworks = 127.0.0.0/8
relay_domains =

smtpd_sasl_type = cyrus
cyrus_sasl_config_path = /etc/postfix/sasl
smtpd_sasl_path = smtpd
smtpd_sasl_auth_enable = yes
smtpd_tls_auth_only = no
smtpd_sasl_security_options = noanonymous

smtpd_relay_restrictions = permit_auth_destination, check_client_access hash:/etc/postfix/access.list, permit_sasl_authenticated, reject_unauth_destination


vim /etc/default/saslauthd
START=yes
MECHANISMS="shadow"


vim /etc/postfix/sasl/smtpd.conf
pwcheck_method: saslauthd
mech_list: PLAIN LOGIN


mkdir -p /opt/mail
chown -R www-data:nogroup /opt/mail
chmod -R 6770 /opt/mail


crontab -e
* * * * * /var/www/s-mail/sys/pcron.py


visudo
www-data ALL = (ALL) NOPASSWD: /var/www/s-mail/sys/pperm.py
nobody ALL = (ALL) NOPASSWD: /var/www/s-mail/sys/pperm.py


> user.auth
username
sha(sha(sha(password)))
base-64-e(pubkey)
base-64-e(hex(iv) + hex(aes-cbc-e(iv, sha(password), prikey)))


> plain.email (email-headers: name \n date \n from \n to \n subject)
base-64-e(iv)
base-64-e(rsa-pub-e(base-64-e(key)))
base-64-e(aes-cbc-e(iv, key, email-headers))
base-64-e(aes-cbc-e(iv, key, email-message))


> secure.email
header-Zsmsg-User: base-64-e(iv) + " " + base-64-e(rsa-pub-e(base-64-e(key)))
header-Subject: base-64-e(aes-cbc-e(iv, key, subject))
data-Message: base-64-e(aes-cbc-e(iv, key, message))


printf "\0postauth\0$p" | base64 > /opt/mail/server.auth


*/
?>
