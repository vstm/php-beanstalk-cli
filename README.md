# Beanstalk CLI

The beanstalk CLI no one asked for. Written in PHP (sue me).

It's using the marvelous [Pheanstalk](https://github.com/pheanstalk/pheanstalk) for talking to a [beanstalkd](https://github.com/beanstalkd/beanstalkd) server. It allows you to list tubes, "manage" jobs and add new jobs to the queue.

## Development Info

The following info is for when you want to hack on it yourself

### Building the phar

For building the phar, [box](https://box-project.github.io/box/) is used. So to build the phar you'll have to [install box](https://box-project.github.io/box/installation/#installation) first. 

I used the global composer install method (just make sure the global bin is in the `PATH` variable, like `export PATH="$PATH:$(composer global config --quiet --global --absolute bin-dir)"`).

```bash
$ box compile
```

If you want to use the docker/podman method you can use the following command:

```bash
$ docker run --rm -v $(which composer):/usr/local/bin/composer -v $PWD:$PWD -w $PWD -ti docker.io/boxproject/box compile
$ podman run --rm -v $(which composer):/usr/local/bin/composer -v $PWD:$PWD -w $PWD -ti docker.io/boxproject/box compile
```

## TODO

- [ ] Write a short README
- [ ] Implement config files for defaults (namely `host` and `port`)
- [x] Abstraction for commands, so they can reuse the beanstalk host and port settings
- [ ] Watch & List separation (the basically do the same, but list is contained in watch)
    - Maybe only make list and then add a watch option
- [x] Implement kick command
- [x] Implement kick-job command
- [x] Implement clear command
- [ ] Implement peek command
