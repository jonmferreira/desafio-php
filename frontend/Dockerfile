FROM node:22-alpine AS base
WORKDIR /app
COPY package*.json ./

# ---- dev: vite dev server com hot-reload ----
FROM base AS dev
RUN npm ci
COPY . .
EXPOSE 3000
CMD ["npm", "run", "dev", "--", "--host"]

# ---- build: gera dist/ ----
FROM base AS build
RUN npm ci
COPY . .
RUN npm run build

# ---- prod: serve dist/ via nginx ----
FROM nginx:1.27-alpine AS prod
COPY --from=build /app/dist /usr/share/nginx/html
COPY nginx.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
