using System.Text.Json;
using System.Text.Json.Serialization;
using Dapper;
using Npgsql;
using AmusedToDeath.Api.Configuration;
using AmusedToDeath.Api.Data;
using AmusedToDeath.Api.Endpoints;
using AmusedToDeath.Api.Security;
using AmusedToDeath.Api.Services;

var builder = WebApplication.CreateBuilder(args);

// ─── Configuration ─────────────────────────────────────────────────────────
builder.Services
    .AddOptions<AppOptions>()
    .Bind(builder.Configuration.GetSection(AppOptions.SectionName));

var appOptions = builder.Configuration.GetSection(AppOptions.SectionName).Get<AppOptions>() ?? new AppOptions();

// ─── JSON: snake_case to match the existing frontend contract ────────────────
// The frontend consumes fields like role_tank, added_date, characterId... The
// original PHP returned raw column names. We standardise on snake_case so the
// C# PascalCase properties serialize to the names the frontend already reads.
builder.Services.ConfigureHttpJsonOptions(options =>
{
    options.SerializerOptions.PropertyNamingPolicy = JsonNamingPolicy.SnakeCaseLower;
    options.SerializerOptions.DictionaryKeyPolicy = JsonNamingPolicy.SnakeCaseLower;
    options.SerializerOptions.DefaultIgnoreCondition = JsonIgnoreCondition.Never;
});

// ─── Database (Npgsql + Dapper) ──────────────────────────────────────────────
// Dapper maps snake_case columns (role_tank) to PascalCase properties (RoleTank).
DefaultTypeMap.MatchNamesWithUnderscores = true;

var connectionString = builder.Configuration.GetConnectionString("Neon")
    ?? throw new InvalidOperationException("ConnectionStrings:Neon is not configured.");
var dataSource = new NpgsqlDataSourceBuilder(connectionString).Build();
builder.Services.AddSingleton(dataSource);
builder.Services.AddSingleton<IDbConnectionFactory, NeonConnectionFactory>();

// ─── Repositories ────────────────────────────────────────────────────────────
builder.Services.AddScoped<CharacterRepository>();
builder.Services.AddScoped<RaidRepository>();
builder.Services.AddScoped<AttendanceRepository>();
builder.Services.AddScoped<ApplicationRepository>();

// ─── Security / session ──────────────────────────────────────────────────────
builder.Services.AddScoped<ICurrentUser, CurrentUser>();
builder.Services.AddScoped<SessionService>();

// ─── External services ───────────────────────────────────────────────────────
builder.Services.AddHttpClient<DiscordWebhookService>();
builder.Services.AddHttpClient<DiscordOAuthService>();
// Battle.net token is application-scoped and cached in the service instance, so
// the service is a singleton that pulls a pooled HttpClient from the factory.
builder.Services.AddHttpClient("battlenet");
builder.Services.AddSingleton<BattleNetService>();

// ─── CORS ────────────────────────────────────────────────────────────────────
const string CorsPolicy = "frontend";
builder.Services.AddCors(options =>
{
    options.AddPolicy(CorsPolicy, policy =>
    {
        policy.WithOrigins(appOptions.CorsOrigins)
            .AllowAnyHeader()
            .AllowAnyMethod()
            .AllowCredentials();
    });
});

builder.Services.AddOpenApi();

var app = builder.Build();

// ─── Database migrations ─────────────────────────────────────────────────────
// Apply any pending migrations on startup (DbUp). Small project, so the tiny
// startup cost is fine; already-applied scripts are skipped. Fails fast if a
// migration errors, so the app never serves against a half-migrated schema.
DatabaseMigrator.Run(connectionString, app.Logger);

if (app.Environment.IsDevelopment())
{
    app.MapOpenApi();
}

app.UseCors(CorsPolicy);

// Resolve the current user from the session cookie on every request.
app.UseMiddleware<SessionMiddleware>();

// ─── Endpoint groups (one group per file) ────────────────────────────────────
app.MapAuthEndpoints();
app.MapCharacterEndpoints();
app.MapRaidEndpoints();
app.MapAttendanceEndpoints();
app.MapApplicationEndpoints();
app.MapBattleNetEndpoints();

app.Run();
