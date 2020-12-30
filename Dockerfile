FROM ubuntu:20.04  

RUN export DEBIAN_FRONTEND=noninteractive DEBCONF_NONINTERACTIVE_SEEN=true && apt-get update \
&& apt-get install -y tzdata \
&& ln -fs /usr/share/zoneinfo/Asia/Taipei /etc/localtime \
&& dpkg-reconfigure --frontend noninteractive tzdata \
&& apt-get install -y software-properties-common gpg-agent --no-install-recommends --no-install-suggests \
&& LC_ALL=C.UTF-8 add-apt-repository -y ppa:ondrej/php \
&& apt-get update \
&& apt-get install -y unzip wget curl apt-transport-https apt-utils curl git-core php7.4-cli php7.4-curl --no-install-recommends --no-install-suggests \
&& apt-get install -y php7.4-xml php7.4-dom php7.4-xsl php7.4-json php7.4-mbstring php7.4-zip php7.4-uuid --no-install-recommends --no-install-suggests \
&& apt-get install -y libcurl3-openssl-dev tesseract-ocr libtesseract-dev --no-install-recommends --no-install-suggests \
&& echo "deb [arch=amd64] http://dl.google.com/linux/chrome/deb/ stable main" | tee /etc/apt/sources.list.d/google-chrome.list \
&& wget https://dl.google.com/linux/linux_signing_key.pub \
&& apt-key add linux_signing_key.pub && rm -f linux_signing_key.pub \
&& apt-get update && apt-get install -y google-chrome-stable --no-install-recommends --no-install-suggests \
&& apt-get clean \
&& cd /root/ \
&& curl -sS https://getcomposer.org/installer | php \
&& php ~/composer.phar require guzzlehttp/guzzle:^6.2 -n \
&& php ~/composer.phar require symfony/dom-crawler:^4.3 -n \
&& php ~/composer.phar require symfony/css-selector:^4.3 -n \
&& php ~/composer.phar require ramsey/uuid:^4.1 -n \
&& php ~/composer.phar require nesbot/carbon:^2.43 -n \
&& php ~/composer.phar require thiagoalessio/tesseract_ocr:^2.9 -n \
&& php ~/composer.phar require nesk/puphpeteer:^2.0 -n \
&& php ~/composer.phar require chrome-php/chrome:^0.8 -n \
&& echo insecure >> $HOME/.curlrc \
&& curl -sL https://raw.githubusercontent.com/creationix/nvm/v0.35.0/install.sh | bash \
&& bash -c "source ./.nvm/nvm.sh && nvm install --lts && npm install @nesk/puphpeteer"

WORKDIR /root/
