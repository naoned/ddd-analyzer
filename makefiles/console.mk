#------------------------------------------------------------------------------
# Onyx Console
#------------------------------------------------------------------------------

-include .env

CONSOLE_IMAGE_NAME=ddd/analyzer
CONTAINER_SOURCE_PATH=/usr/src/ddd
VAR_PATH=${HOST_SOURCE_PATH}/var
ANALYZED_SOURCE_PATH=${VAR_PATH}/project
CONTAINER_VAR_SRC=/var/src

exec = docker run -t -i --rm \
                --name "ddd-analyzer-console" \
                -v ${HOST_SOURCE_PATH}:${CONTAINER_SOURCE_PATH} \
                -v ${ANALYZED_SOURCE_PATH}:${CONTAINER_VAR_SRC} \
                -u ${USER_ID}:${GROUP_ID} \
                -w ${CONTAINER_SOURCE_PATH} \
                ${CONSOLE_IMAGE_NAME} \
                $1

console = $(call exec, ./console $1)

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

analyze: create-console-image pull-project ## Launch analyze and Console reporting
	$(call console, -vvv analyze ${CONTAINER_VAR_SRC}/src)

analyze-html: create-console-image pull-project ## Launch analyze and HTML reporting
	$(call console, -vvv analyze --no-output --htmlReport report.html ${CONTAINER_VAR_SRC}/src)

analyze-json: create-console-image pull-project ## Launch quiet analyze and JSON reporting
	$(call console, -vvv analyze --no-output --jsonReport var/report.json ${CONTAINER_VAR_SRC}/src)

${ANALYZED_SOURCE_PATH}:
	git clone ${ANALYZED_SOURCE_REPO} ${ANALYZED_SOURCE_PATH}
	
pull-project: ${ANALYZED_SOURCE_PATH}
	cd ${ANALYZED_SOURCE_PATH}; \
	git pull; \
	git rev-parse HEAD >${VAR_PATH}/commit.hash

#------------------------------------------------------------------------------

bash: create-console-image ## Connect to console container
	$(call exec, bash)

meminfo: create-console-image ## Launch meminfo analyze (need dump.json in project dir)
	$(call exec, /php-meminfo/analyzer/bin/analyzer summary dump.json)

#------------------------------------------------------------------------------

clean-console:
	-docker rmi ${CONSOLE_IMAGE_NAME}

#------------------------------------------------------------------------------

.PHONY: create-console-image console clean-console
