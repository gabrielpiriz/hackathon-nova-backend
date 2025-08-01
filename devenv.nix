{ pkgs, config, ... }:

{
    cachix.enable = false;
    dotenv.disableHint = true;

  # https://devenv.sh/packages/
    packages = [
        pkgs.poppler_utils
        pkgs.php84
        pkgs.php84Packages.composer
        pkgs.nodejs_22
    ];

    #Languages
    languages.php = {
        enable = true;
        package = pkgs.php84.buildEnv {
            extensions = { all, enabled }: with all; enabled ++ [ xdebug ];

            extraConfig = ''
                memory_limit = 256m
                xdebug.mode = debug
                xdebug.start_with_request = yes
                xdebug.idekey = vscode
                xdebug.log_level = 0
            '';
        };
    };

    languages.php.fpm.pools.web = {
        settings = {
            "clear_env" = "no";
            "pm" = "dynamic";
            "pm.max_children" = 10;
            "pm.min_spare_servers" = 1;
            "pm.max_spare_servers" = 10;
        };
    };

    languages.javascript.enable = true;

    certificates = [
        "asado.localhost"
    ];

    scripts.caddy-setcap.exec = ''
        sudo setcap 'cap_net_bind_service=+ep' ${pkgs.caddy}/bin/caddy
    '';

    services.caddy = {
        enable = true;
        virtualHosts."asado.localhost" = {
            extraConfig = ''
                tls ${config.env.DEVENV_STATE}/mkcert/asado.localhost.pem ${config.env.DEVENV_STATE}/mkcert/asado.localhost-key.pem
                root * public
                php_fastcgi unix/${config.languages.php.fpm.pools.web.socket}
                file_server
            '';
        };
    };

    services.mailpit.enable = true;

    # Processes
    processes.assets-watch.exec = "npm run dev";

    # On shell enter

    enterShell = ''
        if [[ ! -d vendor ]]; then
            composer install
        fi

        if [[ ! -d node_modules ]]; then
            npm install --no-save
        fi

        if [ ! -f .env ]; then
            cp .env.example .env
            php artisan key:generate
        fi
    '';
}
