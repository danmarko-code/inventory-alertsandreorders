### inventory-alertsandreorders remote repo

- Clone niyo lang itong repo sa laragon/www ninyo.
- Pagkaclone, punta kayo laragon, open niyo server, open niyo terminal, navigate kayo papuntang folder neto - cd inventory-alertsandreorders
- then copy niyo to sa terminal - composer install
- wait niyo lang saglit yun "Generating optimized autoload files", magpapakita na mga DONE after nyan
- Pag tapos na, copy niyo to - cp .env.example .env
- Punta kayo sa vscode, open niyo tong folder na to, hanapin niyo yun .env files. Hanapin niyo to

DB_CONNECTION=sqlite  
DB_HOST=127.0.0.1  
DB_PORT=3306  
DB_DATABASE=laravel  
DB_USERNAME=root  
DB_PASSWORD=  

- Then palitan niyo yang lahat neto

DB_CONNECTION=mysql  
DB_HOST=127.0.0.1  
DB_PORT=3306  
DB_DATABASE="inventory-alertsandreorders"  
DB_USERNAME=root  
DB_PASSWORD=  

  - After nyan, copy niyo ulit to - php artisan key:generate
  - Wait niyo. Then after niyan, eto - php artisan migrate:fresh --seed
 
  - Then open niyo sa laragon. Right click -> www tapos name ng folder project

