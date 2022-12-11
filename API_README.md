# MyClimate API

## Table of contents
1. [Author](#1-author)
2. [About MyClimate project](#2-about-myclimate-api-project)
3. [Hours spent]()

## 1. Author
Marc Vivas Baiges

## 2. About MyClimate API project
This system allows users to remote managing information related with
climate installation homes, sensors and users within a web services system.

## Built with
The project is built with **Laravel 9** which is a php framework.

## UML Class Diagram
![UML Class Diagram](api_uml_diagram.png)

## API Docs
The API endpoints are documented in the file named `api_doc.yaml`. In order to preview 
the API endpoints in a friendly UI and interact with it, 
open [Swagger Editor](https://editor.swagger.io/) and import the file.  

## Project structure
In case you have never seen a Laravel project, it can be quite hard to 
find the code that really matters. For this reason, now I will show you where
are the most important files.

## Hours spent
- December 2 2022: 16:30 - 19:30 Project planning ->  <strong> 3 hours </strong>  
- December 3 2022: 9:00 - 17:30 Project development -> <strong> 8.5 hours </strong> 
- December 4 2022: 8:00 - 15:00 Project development -> <strong> 7 hours </strong>
- December 5 2022: 16:30 - 19:00 Project development -> <strong> 2.5 hours </strong>
- December 6 2022: 7:00 - 11:30 Project development -> <strong> 4.5 hours </strong>
- December 7 2022: 16:00 - 18:00 Project development (Finished all endpoints) -> <strong> 2 hours </strong>
- December 8 2022: 9:00 - 14:00 Include project 1 -> <strong> 5 hours </strong>
- December 9 2022: 8:30 - 10:00 Documentation -> <strong> 1.30 hours </strong>
- <strong>  Total:   ?? hours  </strong> 

## How to run the service
You should have installed `docker compose` in your system.

To run only the API you have to insert the following command:

```bash
sudo docker compose up MyClimateAPI
```
## References
1. API token authentication: https://laravel.com/docs/9.x/sanctum#api-token-authentication