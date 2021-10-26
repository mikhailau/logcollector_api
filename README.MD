# LogCollector API
## Installation

### install docker
https://docs.docker.com/engine/install/ubuntu/

### Elastic
```
 sudo docker run -p 9200:9200 -p 9300:9300 -e "discovery.type=single-node" docker.elastic.co/elasticsearch/elasticsearch:7.14.1
```

### Filebeat
1. ```cd DockerEnv```
2. change *IP_OF_ELASTIC_CONTAINER_OR_HOST_IP*  to host IP
3. Run command
```
sudo docker run --name=filebeat \
  --user=root \
  --volume="$(pwd)/filebeat.docker.yml:/usr/share/filebeat/filebeat.yml:ro" \
  --volume="/var/lib/docker/containers:/var/lib/docker/containers:ro" \
  --volume="/var/run/docker.sock:/var/run/docker.sock:ro" \
  --volume="/var/log:/logs:ro" \
  --volume "$(pwd)/apache.yml:/usr/share/filebeat/modules.d/apache.yml" \
  --volume "$(pwd)/nginx.yml:/usr/share/filebeat/modules.d/nginx.yml" \
  docker.elastic.co/beats/filebeat:7.14.1 filebeat -e -strict.perms=false \
  -E output.elasticsearch.hosts=["IP_OF_ELASTIC_CONTAINER_OR_HOST:9200"]
```

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
  --header 'php-auth-pw: password' \
  --header 'php-auth-user: user' \
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
  --header 'php-auth-pw: password' \
  --header 'php-auth-user: user' \
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
  --header 'php-auth-pw: password' \
  --header 'php-auth-user: user' \
  --data '{
	"page": 1,
	"perPage": "10",
	"search": ["/([0-9]{1,3}.){3}2/", "nginx"],
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