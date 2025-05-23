FROM nginx:stable-alpine

COPY html/ /usr/share/nginx/html/

COPY nginx/nginx.conf /etc/nginx/nginx.conf

EXPOSE 80 443

CMD ["nginx", "-g", "daemon off;"]
