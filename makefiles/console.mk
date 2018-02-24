#------------------------------------------------------------------------------
# Onyx Console
#------------------------------------------------------------------------------

-include .env

CONSOLE_IMAGE_NAME=ddd/analyzer
CONTAINER_SOURCE_PATH=/usr/src/ddd
ANALYZED_SOURCE_PATH=${HOST_SOURCE_PATH}/${ANALYZED_RELATIVE_SOURCE_PATH}
CONTAINER_VAR_SRC=/var/src

console = docker run -t -i --rm \
                --name "ddd-analyzer-console" \
                -v ${HOST_SOURCE_PATH}:${CONTAINER_SOURCE_PATH} \
                -v ${ANALYZED_SOURCE_PATH}:${CONTAINER_VAR_SRC} \
                -u ${USER_ID}:${GROUP_ID} \
                -w ${CONTAINER_SOURCE_PATH} \
                ${CONSOLE_IMAGE_NAME} \
                ./console $1

# Spread cli arguments
ifneq (,$(filter $(firstword $(MAKECMDGOALS)),console))
    CLI_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
    ESCAPED_CLI_ARGS = $(subst :,,${CLI_ARGS})
    $(eval $(ESCAPED_CLI_ARGS):;@:)
endif

#------------------------------------------------------------------------------

create-console-image: docker/images/console/Dockerfile
	docker build -q --build-arg UID=${USER_ID} -t ${CONSOLE_IMAGE_NAME} docker/images/console/

console: create-console-image ## Run console command
	$(call console, ${CLI_ARGS})

analyze-html: create-console-image ## Run console command
	$(call console, -vvv analyze --htmlReport report.html ${CONTAINER_VAR_SRC}/src)

analyze-json: create-console-image ## Run console command
	$(call console, -vvv analyze --no-output --jsonReport report.json ${CONTAINER_VAR_SRC}/src)

#------------------------------------------------------------------------------

clean-console:
	-docker rmi ${CONSOLE_IMAGE_NAME}

#------------------------------------------------------------------------------

.PHONY: create-console-image console clean-console
