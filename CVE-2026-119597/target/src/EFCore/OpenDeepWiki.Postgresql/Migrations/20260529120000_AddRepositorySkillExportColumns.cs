using System;
using Microsoft.EntityFrameworkCore.Infrastructure;
using Microsoft.EntityFrameworkCore.Migrations;

#nullable disable

namespace OpenDeepWiki.Postgresql.Migrations
{
    /// <inheritdoc />
    [DbContext(typeof(PostgresqlDbContext))]
    [Migration("20260529120000_AddRepositorySkillExportColumns")]
    public partial class AddRepositorySkillExportColumns : Migration
    {
        /// <inheritdoc />
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.AddColumn<bool>(
                name: "GenerateSkill",
                table: "Repositories",
                type: "boolean",
                nullable: false,
                defaultValue: true);

            migrationBuilder.AddColumn<DateTime>(
                name: "SkillGeneratedAt",
                table: "BranchLanguages",
                type: "timestamp with time zone",
                nullable: true);

            migrationBuilder.AddColumn<string>(
                name: "SkillMarkdown",
                table: "BranchLanguages",
                type: "text",
                nullable: true);
        }

        /// <inheritdoc />
        protected override void Down(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.DropColumn(
                name: "GenerateSkill",
                table: "Repositories");

            migrationBuilder.DropColumn(
                name: "SkillGeneratedAt",
                table: "BranchLanguages");

            migrationBuilder.DropColumn(
                name: "SkillMarkdown",
                table: "BranchLanguages");
        }
    }
}
