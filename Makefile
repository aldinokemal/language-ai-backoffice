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

# Manual deployment targets
build-base-arm64:
	@echo "Building base image for ARM64 platform..."
	@docker buildx build --platform linux/arm64 --load -t $(IMAGE_NAME):base-arm64 -f $(BASE_DOCKERFILE) .

build-base-amd64:
	@echo "Building base image for AMD64 platform..."
	@docker buildx build --platform linux/amd64 --load -t $(IMAGE_NAME):base-amd64 -f $(BASE_DOCKERFILE) .

push-base-arm64:
	@echo "Pushing base image for ARM64 platform..."
	@docker push $(IMAGE_NAME):base-arm64

push-base-amd64:
	@echo "Pushing base image for AMD64 platform..."
	@docker push $(IMAGE_NAME):base-amd64

build-base-all: build-base-arm64 build-base-amd64
	@echo "Built base images for both platforms"

push-base-all: push-base-arm64 push-base-amd64
	@echo "Pushed base images for both platforms"

deploy-base-manual: build-base-all push-base-all buildx-push-base
	@echo "Manual deployment completed for base images"

