lancer les containers

docker compose up -d 


sauvegarder la bsdonne

docker exec -i mysql-container mysqldump -u root -proot airlockunlock > back_airlockunlock.sql  
docker exec -i mysql-container mysqldump -u root -proot Tapkey > back_tapkey.sql  

docker-compose down
 
apres tu commit et tu push 

recuperer ma base de donnés sur un autre pc

docker exec -i mysql-container mysql -u root -proot airlockunlock < back_airlockunlock.sql  
docker exec -i mysql-container mysql -u root -proot Tapkey < back_tapkey.sql 

git fetch origin
git reset --hard origin/API-1