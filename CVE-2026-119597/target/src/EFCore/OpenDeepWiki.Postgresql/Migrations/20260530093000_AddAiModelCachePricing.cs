using Microsoft.EntityFrameworkCore.Infrastructure;
using Microsoft.EntityFrameworkCore.Migrations;

#nullable disable

namespace OpenDeepWiki.Postgresql.Migrations
{
    [DbContext(typeof(PostgresqlDbContext))]
    [Migration("20260530093000_AddAiModelCachePricing")]
    public partial class AddAiModelCachePricing : Migration
    {
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.AddColumn<decimal>(
                name: "CacheHitTokenPrice",
                table: "AiModelConfigs",
                type: "numeric(18,8)",
                nullable: true);

            migrationBuilder.AddColumn<decimal>(
                name: "CacheCreationTokenPrice",
                table: "AiModelConfigs",
                type: "numeric(18,8)",
                nullable: true);

            migrationBuilder.AddColumn<decimal>(
                name: "CacheHitTokenPrice",
                table: "TokenUsages",
                type: "numeric(18,8)",
                nullable: true);

            migrationBuilder.AddColumn<decimal>(
                name: "CacheCreationTokenPrice",
                table: "TokenUsages",
                type: "numeric(18,8)",
                nullable: true);
        }

        protected override void Down(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.DropColumn(
                name: "CacheHitTokenPrice",
                table: "AiModelConfigs");

            migrationBuilder.DropColumn(
                name: "CacheCreationTokenPrice",
                table: "AiModelConfigs");

            migrationBuilder.DropColumn(
                name: "CacheHitTokenPrice",
                table: "TokenUsages");

            migrationBuilder.DropColumn(
                name: "CacheCreationTokenPrice",
                table: "TokenUsages");
        }
    }
}
