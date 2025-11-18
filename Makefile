IMAGE_NAME=registry.hydrogendioxide.net/languageai/backoffice
DOCKERFILE=./docker/dockerfile/app.Dockerfile
BASE_DOCKERFILE=./docker/dockerfile/base.Dockerfile
VERSION ?= latest

buildx-push:
	@echo "Building and pushing multi-arch image with buildx (no cache)..."
	@TAGS=""; \
	for tag in $$(echo $(VERSION) | tr "," " "); do \
		TAGS="$$TAGS --tag $(IMAGE_NAME):$$tag"; \
	done; \
	docker buildx build \
		$$TAGS \
		--platform linux/amd64,linux/arm64 \
		--push \
		--no-cache \
		-f $(DOCKERFILE) \
		.

buildx-push-base:
	@echo "Building and pushing multi-arch image with buildx..."
	@make update-chrome
	@docker buildx build \
		--tag $(IMAGE_NAME):base \
		--platform linux/amd64,linux/arm64 \
		--push \
		-f $(BASE_DOCKERFILE) \
		.

update-chrome:
	@echo "Updating Chrome headless version to latest stable..."
	@cd docker && ./update_chrome_version.sh
