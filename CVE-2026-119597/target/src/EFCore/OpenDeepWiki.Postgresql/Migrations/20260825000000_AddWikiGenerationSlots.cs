using System;
using Microsoft.EntityFrameworkCore.Infrastructure;
using Microsoft.EntityFrameworkCore.Migrations;
using OpenDeepWiki.Postgresql;

#nullable disable

namespace OpenDeepWiki.Postgresql.Migrations
{
    [DbContext(typeof(PostgresqlDbContext))]
    [Migration("20260825000000_AddWikiGenerationSlots")]
    public partial class AddWikiGenerationSlots : Migration
    {
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.AddColumn<string>(
                name: "InstanceId",
                table: "RepositoryGenerationLocks",
                type: "character varying(128)",
                maxLength: 128,
                nullable: true);
            migrationBuilder.AddColumn<DateTime>(
                name: "HeartbeatAt",
                table: "RepositoryGenerationLocks",
                type: "timestamp with time zone",
                nullable: true);

            migrationBuilder.CreateTable(
                name: "WikiGenerationSlots",
                columns: table => new
                {
                    Id = table.Column<string>(type: "text", maxLength: 36, nullable: false),
                    SlotIndex = table.Column<int>(type: "integer", nullable: false),
                    WorkType = table.Column<int>(type: "integer", nullable: true),
                    RepositoryId = table.Column<string>(type: "character varying(36)", maxLength: 36, nullable: true),
                    OwnerId = table.Column<string>(type: "character varying(36)", maxLength: 36, nullable: true),
                    InstanceId = table.Column<string>(type: "character varying(128)", maxLength: 128, nullable: true),
                    AcquiredAt = table.Column<DateTime>(type: "timestamp with time zone", nullable: true),
                    HeartbeatAt = table.Column<DateTime>(type: "timestamp with time zone", nullable: true),
                    CreatedAt = table.Column<DateTime>(type: "timestamp with time zone", nullable: false),
                    UpdatedAt = table.Column<DateTime>(type: "timestamp with time zone", nullable: true),
                    DeletedAt = table.Column<DateTime>(type: "timestamp with time zone", nullable: true),
                    IsDeleted = table.Column<bool>(type: "boolean", nullable: false),
                    Version = table.Column<byte[]>(type: "bytea", rowVersion: true, nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_WikiGenerationSlots", x => x.Id);
                });

            migrationBuilder.CreateIndex(
                name: "IX_WikiGenerationSlots_SlotIndex",
                table: "WikiGenerationSlots",
                column: "SlotIndex",
                unique: true);
        }

        protected override void Down(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.DropTable(name: "WikiGenerationSlots");
            migrationBuilder.DropColumn(name: "InstanceId", table: "RepositoryGenerationLocks");
            migrationBuilder.DropColumn(name: "HeartbeatAt", table: "RepositoryGenerationLocks");
        }
    }
}
