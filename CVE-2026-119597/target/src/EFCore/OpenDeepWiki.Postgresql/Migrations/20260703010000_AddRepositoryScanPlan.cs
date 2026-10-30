using Microsoft.EntityFrameworkCore.Infrastructure;
using Microsoft.EntityFrameworkCore.Migrations;

#nullable disable

namespace OpenDeepWiki.Postgresql.Migrations
{
    [DbContext(typeof(PostgresqlDbContext))]
    [Migration("20260703010000_AddRepositoryScanPlan")]
    public partial class AddRepositoryScanPlan : Migration
    {
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.AddColumn<int>(
                name: "ScanDepthMode",
                table: "Repositories",
                type: "integer",
                nullable: false,
                defaultValue: 0);

            migrationBuilder.AddColumn<int>(
                name: "DirectoryTreeDepthOverride",
                table: "Repositories",
                type: "integer",
                nullable: true);

            migrationBuilder.AddColumn<int>(
                name: "FileListDepthOverride",
                table: "Repositories",
                type: "integer",
                nullable: true);

            migrationBuilder.AddColumn<int>(
                name: "MaxTreeNodes",
                table: "Repositories",
                type: "integer",
                nullable: true);

            migrationBuilder.AddColumn<int>(
                name: "MaxFilesPerDirectory",
                table: "Repositories",
                type: "integer",
                nullable: true);

            migrationBuilder.AddColumn<int>(
                name: "MaxTotalFiles",
                table: "Repositories",
                type: "integer",
                nullable: true);

            migrationBuilder.AddColumn<string>(
                name: "ExtraExcludedDirsJson",
                table: "Repositories",
                type: "character varying(2000)",
                maxLength: 2000,
                nullable: true);

            migrationBuilder.AddColumn<string>(
                name: "ScanProfileHash",
                table: "Repositories",
                type: "character varying(128)",
                maxLength: 128,
                nullable: true);

            migrationBuilder.AddColumn<string>(
                name: "ScanProfileReason",
                table: "Repositories",
                type: "character varying(1000)",
                maxLength: 1000,
                nullable: true);

            migrationBuilder.AddColumn<double>(
                name: "ScanProfileConfidence",
                table: "Repositories",
                type: "double precision",
                nullable: true);

            migrationBuilder.AddColumn<DateTime>(
                name: "ScanProfileUpdatedAt",
                table: "Repositories",
                type: "timestamp with time zone",
                nullable: true);
        }

        protected override void Down(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.DropColumn(name: "ScanDepthMode", table: "Repositories");
            migrationBuilder.DropColumn(name: "DirectoryTreeDepthOverride", table: "Repositories");
            migrationBuilder.DropColumn(name: "FileListDepthOverride", table: "Repositories");
            migrationBuilder.DropColumn(name: "MaxTreeNodes", table: "Repositories");
            migrationBuilder.DropColumn(name: "MaxFilesPerDirectory", table: "Repositories");
            migrationBuilder.DropColumn(name: "MaxTotalFiles", table: "Repositories");
            migrationBuilder.DropColumn(name: "ExtraExcludedDirsJson", table: "Repositories");
            migrationBuilder.DropColumn(name: "ScanProfileHash", table: "Repositories");
            migrationBuilder.DropColumn(name: "ScanProfileReason", table: "Repositories");
            migrationBuilder.DropColumn(name: "ScanProfileConfidence", table: "Repositories");
            migrationBuilder.DropColumn(name: "ScanProfileUpdatedAt", table: "Repositories");
        }
    }
}
