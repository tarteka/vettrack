# 1 Build Angular
FROM node:26 AS build

WORKDIR /app
COPY app/ .

RUN npm ci
RUN npx ng build --configuration production

# 2 Runtime: Nginx
FROM nginx:alpine

COPY /docker/nginx/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=build /app/dist /usr/share/nginx/html