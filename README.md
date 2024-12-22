## About Project

The project is aimed at demonstrating backend capability to fetch news data from various third part service using their API and store it in db. There is no authentication/authorization implemented for the sake of simplicity. The project also exposes an API endpoint to get the stored news articles from the database and filter them as per following query parameters:

- q: free text search 
- author: free text search for article authors
- date: exact date search of the aritcles
- min_date: minimum date of the article to be searched. The format can be any parseable date by Carbon 
- max_date: maximum date of the article to be searched. The format can be any parseable date by Carbon
- source: the datasource of the article. The possible values are news_api, guardian_api, nytimes_api
