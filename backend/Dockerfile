FROM mcr.microsoft.com/dotnet/sdk:10.0-alpine@sha256:230c3fbae0ceedc70bca04eed2bfb5694e8b248cadbb81010f41b7a993601f55 AS publish

WORKDIR /src
COPY backend/AmusedToDeath.Api.csproj backend/
RUN dotnet restore backend/AmusedToDeath.Api.csproj
COPY backend/ backend/
RUN dotnet publish backend/AmusedToDeath.Api.csproj -c Release --no-restore -o /out /p:UseAppHost=false

FROM mcr.microsoft.com/dotnet/aspnet:10.0-alpine@sha256:819bd3649d8bf53f35527c68d62e17b348ef6f24ae73073f497f00a878348062

RUN apk add --no-cache curl
WORKDIR /app
COPY --from=publish --chown=1654:1654 /out/ ./

ENV ASPNETCORE_HTTP_PORTS=8080
EXPOSE 8080
USER 1654:1654
HEALTHCHECK --interval=10s --timeout=5s --start-period=20s --retries=6 CMD curl --fail --silent --max-time 3 http://127.0.0.1:8080/api/health || exit 1
ENTRYPOINT ["dotnet", "AmusedToDeath.Api.dll"]
