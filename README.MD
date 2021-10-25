# LogCollector API
## Installation
1) Install dependencies
```
sudo apt-get install php7.4-dev
sudo apt-get install libpam0g-dev
sudo pecl install PAM

#add You should add "extension=pam.so" to php.ini(/etc/php/7.4/fpm/php.ini )
```
2) clone repository
3) `composer install`
4) Start server `symfony server:start`
## Query examples

### Simple query with pagination
```
curl --request POST \
  --url http://10.211.55.3:8000/api/search \
  --header 'Content-Type: application/json' \
  --data '{
	"page": 2,
	"perPage": "100",
	"search": "apache"
}'
```
### Regexp
***Attantion!*** Symbols `= && || > < " ~ : \ AND OR NOT space` are escaped

```
curl --request POST \
  --url http://10.211.55.3:8000/api/search \
  --header 'Content-Type: application/json' \
  --data '{
	"page": 1,
	"perPage": "100",
	"search": "/([0-9]{1,3}.){3}2/"
}'
```


### support for multiple occurrences of the same filter
```
curl --request POST \
  --url http://10.211.55.3:8000/api/search \
  --header 'Content-Type: application/json' \
  --data '{
	"page": 1,
	"perPage": "10",
	"search": ["/([0-9]{1,3}.){3}2/", "passwd"],
	"timestamp": [
		{
			"from":"2021/10/11"
		},
		{
			"from":"2020-10-10 10:00:00",
			"to":"2021-10-10 23:59:59"
		}
		
	]
}'
```