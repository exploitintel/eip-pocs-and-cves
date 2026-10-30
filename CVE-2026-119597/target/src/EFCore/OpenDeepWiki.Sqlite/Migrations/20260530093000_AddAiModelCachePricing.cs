using Microsoft.EntityFrameworkCore.Infrastructure;
using Microsoft.EntityFrameworkCore.Migrations;

#nullable disable

namespace OpenDeepWiki.Sqlite.Migrations
{
    [DbContext(typeof(SqliteDbContext))]
    [Migration("20260530093000_AddAiModelCachePricing")]
    public partial class AddAiModelCachePricing : Migration
    {
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.AddColumn<string>(
                name: "CacheHitTokenPrice",
                table: "AiModelConfigs",
                type: "TEXT",
                nullable: true);

            migrationBuilder.AddColumn<string>(
                name: "CacheCreationTokenPrice",
                table: "AiModelConfigs",
                type: "TEXT",
                nullable: true);

            migrationBuilder.AddColumn<string>(
                name: "CacheHitTokenPrice",
                table: "TokenUsages",
                type: "TEXT",
                nullable: true);

            migrationBuilder.AddColumn<string>(
                name: "CacheCreationTokenPrice",
                table: "TokenUsages",
                type: "TEXT",
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
