Getting started
---------------
1 . Edit `.env`:
`ANALYZED_SOURCE_REPO={link_github_repository}`
`ANALYZED_BRANCH={branch_to_analyze}`
`KARMA_ENV={dev or prod}`
2 . `make init`
3 . `make webpack` or `make webpack-prod`
4 . `make up`
5 . `make analyze-json`

See Graph
------------------
Go to `http://localhost`

Deployment
------------------

In production, add this line to `.env` file :
`DOCKER_COMPOSE_YML=docker/docker-compose.prod.yml`

Do not forget to give a `vhost.conf` in `system/apache` directory
See "Getting started" to see how to start application

To add analyze job to crontab, create `ddd-analyzer` file in `/etc/cron.d` directory with this content:
`* 20 * * * naoned cd /home/naoned/workspace/ddd-analyzer && ENV_INTERACTIVE=false make analyze-json 2>&1`