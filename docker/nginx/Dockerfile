server {
    listen 80;
    server_name logisteia.com www.logisteia.com localhost;
    root /var/www/html;
    index index.php index.html;

    # 1. Frontend: Todo lo que no sea PHP lo maneja Angular
    location / {
        root /usr/share/nginx/html;
        try_files $uri $uri/ /index.html;
    }

    # 2. Backend: Archivos PHP
    location ~ \.php$ {
        root /var/www/html;
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass backend:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }

    # Aumentar tamaño de subida para archivos grandes
    client_max_body_size 64M;
}