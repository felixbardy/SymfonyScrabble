
default:
	@echo "Please choose a rule to execute (start, stop)"

start:
	@echo "••• Starting Symfony server..."
	symfony server:start -d

stop:
	@echo "••• Stopping Symfony server..."
	symfony server:stop
