# LogCollector API

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