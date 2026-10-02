FROM node:26

WORKDIR /usr/src/app

# Copiamos SOLO los package.json desde app (para cache eficiente)
COPY --chown=node:node ./app/package*.json ./

RUN npm install

# Copiamos el resto del proyecto Angular
COPY --chown=node:node ./app/ ./

USER node

CMD ["npx", "ng", "serve", "--host", "0.0.0.0"]