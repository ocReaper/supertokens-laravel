(cd ../../../com-root && nohup java -Djava.security.egd=file:/dev/urandom -classpath "./core/*:./plugin-interface/*" io.supertokens.Main ./ DEV >/dev/null 2>&1 &)