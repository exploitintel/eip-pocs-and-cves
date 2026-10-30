using Microsoft.EntityFrameworkCore.Infrastructure;
using Microsoft.EntityFrameworkCore.Migrations;

#nullable disable

namespace OpenDeepWiki.Sqlite.Migrations
{
    [DbContext(typeof(SqliteDbContext))]
    [Migration("20260703010000_AddRepositoryScanPlan")]
    public partial class AddRepositoryScanPlan : Migration
    {
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.AddColumn<int>(
                name: "ScanDepthMode",
                table: "Repositories",
                type: "INTEGER",
                nullable: false,
                defaultValue: 0);

            migrationBuilder.AddColumn<int>(
                name: "DirectoryTreeDepthOverride",
                table: "Repositories",
                type: "INTEGER",
                nullable: true);

            migrationBuilder.AddColumn<int>(
                name: "FileListDepthOverride",
                table: "Repositories",
                type: "INTEGER",
                nullable: true);

            migrationBuilder.AddColumn<int>(
                name: "MaxTreeNodes",
                table: "Repositories",
                type: "INTEGER",
                nullable: true);

            migrationBuilder.AddColumn<int>(
                name: "MaxFilesPerDirectory",
                table: "Repositories",
                type: "INTEGER",
                nullable: true);

            migrationBuilder.AddColumn<int>(
                name: "MaxTotalFiles",
                table: "Repositories",
                type: "INTEGER",
                nullable: true);

            migrationBuilder.AddColumn<string>(
                name: "ExtraExcludedDirsJson",
                table: "Repositories",
                type: "TEXT",
                maxLength: 2000,
                nullable: true);

            migrationBuilder.AddColumn<string>(
                name: "ScanProfileHash",
                table: "Repositories",
                type: "TEXT",
                maxLength: 128,
                nullable: true);

            migrationBuilder.AddColumn<string>(
                name: "ScanProfileReason",
                table: "Repositories",
                type: "TEXT",
                maxLength: 1000,
                nullable: true);

            migrationBuilder.AddColumn<double>(
                name: "ScanProfileConfidence",
                table: "Repositories",
                type: "REAL",
                nullable: true);

            migrationBuilder.AddColumn<DateTime>(
                name: "ScanProfileUpdatedAt",
                table: "Repositories",
                type: "TEXT",
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
