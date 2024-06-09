https://www.digitalocean.com/community/tutorials/how-to-create-a-self-signed-ssl-certificate-for-apache-in-ubuntu-16-04

- sudo openssl dhparam -out /etc/ssl/certs/dhparam.pem 2048
- sudo nano /etc/apache2/conf-available/ssl-params.conf




## SETTING THE APACHE PROXY PASS
	<VirtualHost *:443>
					
			ServerName api-highschool-scadware.eaproc.com
		
			# Note that the URL must end with / so it can append all queries
			# you must enable it to work
			# sudo a2enmod proxy
			# sudo a2enmod proxy_http
			ProxyPreserveHost on
			
			SSLProxyEngine On
			SSLProxyVerify none 
		    	SSLProxyCheckPeerCN off
			SSLProxyCheckPeerName off
			SSLProxyCheckPeerExpire off
    
			ProxyPass	/	https://localhost:10181/
			ProxyPassReverse	/	https://localhost:10181/
				
							
			SSLEngine on
	
	
			SSLCertificateFile	/etc/apache2/ssl.crt/eaproc.crt
			SSLCertificateKeyFile /etc/apache2/ssl.crt/eaproc.key
	
			SSLCACertificateFile /etc/apache2/ssl.crt/eaproc_bundle.crt
	
	
			<FilesMatch "\.(cgi|shtml|phtml|php)$">
					SSLOptions +StdEnvVars
			</FilesMatch>
			<Directory /usr/lib/cgi-bin>
					SSLOptions +StdEnvVars
			</Directory>
				
	</VirtualHost>



