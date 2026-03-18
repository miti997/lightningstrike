compose_command := docker compose -p lightningstrike -f docker/docker-compose.yml

up:
	$(compose_command) up -d

down:
	$(compose_command) down

build:
	$(compose_command) build

remake: down build up
