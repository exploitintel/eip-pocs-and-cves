using System;
using Microsoft.EntityFrameworkCore.Infrastructure;
using Microsoft.EntityFrameworkCore.Migrations;
using OpenDeepWiki.Sqlite;

#nullable disable

namespace OpenDeepWiki.Sqlite.Migrations
{
    [DbContext(typeof(SqliteDbContext))]
    [Migration("20260825000000_AddWikiGenerationSlots")]
    public partial class AddWikiGenerationSlots : Migration
    {
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.AddColumn<string>(
                name: "InstanceId",
                table: "RepositoryGenerationLocks",
                type: "TEXT",
                maxLength: 128,
                nullable: true);
            migrationBuilder.AddColumn<DateTime>(
                name: "HeartbeatAt",
                table: "RepositoryGenerationLocks",
                type: "TEXT",
                nullable: true);

            migrationBuilder.CreateTable(
                name: "WikiGenerationSlots",
                columns: table => new
                {
                    Id = table.Column<string>(type: "TEXT", maxLength: 36, nullable: false),
                    SlotIndex = table.Column<int>(type: "INTEGER", nullable: false),
                    WorkType = table.Column<int>(type: "INTEGER", nullable: true),
                    RepositoryId = table.Column<string>(type: "TEXT", maxLength: 36, nullable: true),
                    OwnerId = table.Column<string>(type: "TEXT", maxLength: 36, nullable: true),
                    InstanceId = table.Column<string>(type: "TEXT", maxLength: 128, nullable: true),
                    AcquiredAt = table.Column<DateTime>(type: "TEXT", nullable: true),
                    HeartbeatAt = table.Column<DateTime>(type: "TEXT", nullable: true),
                    CreatedAt = table.Column<DateTime>(type: "TEXT", nullable: false),
                    UpdatedAt = table.Column<DateTime>(type: "TEXT", nullable: true),
                    DeletedAt = table.Column<DateTime>(type: "TEXT", nullable: true),
                    IsDeleted = table.Column<bool>(type: "INTEGER", nullable: false),
                    Version = table.Column<byte[]>(type: "BLOB", rowVersion: true, nullable: true)
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
