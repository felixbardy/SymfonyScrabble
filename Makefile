
default:
	@echo "Please choose a rule to execute (start, stop)"

start:
	@echo "••• Starting Docker services..."
	systemctl status docker.service >/dev/null || systemctl start docker.service
	@echo "••• Starting containers..."
	docker-compose up -d
	@echo "••• Starting Symfony server..."
	symfony server:start -d

stop:
	@echo "••• Stopping Symfony server..."
	symfony server:stop
	@echo "••• Stpping Docker containers..."
	docker-compose stop
