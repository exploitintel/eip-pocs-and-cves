using System;
using Microsoft.EntityFrameworkCore.Migrations;

#nullable disable

namespace OpenDeepWiki.Postgresql.Migrations
{
    /// <inheritdoc />
    public partial class AddGraphifyArtifacts : Migration
    {
        /// <inheritdoc />
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.CreateTable(
                name: "GraphifyArtifacts",
                columns: table => new
                {
                    Id = table.Column<string>(type: "text", nullable: false),
                    RepositoryId = table.Column<string>(type: "character varying(36)", maxLength: 36, nullable: false),
                    RepositoryBranchId = table.Column<string>(type: "character varying(36)", maxLength: 36, nullable: false),
                    Status = table.Column<int>(type: "integer", nullable: false),
                    CommitId = table.Column<string>(type: "character varying(80)", maxLength: 80, nullable: true),
                    OutputRoot = table.Column<string>(type: "character varying(500)", maxLength: 500, nullable: true),
                    EntryFilePath = table.Column<string>(type: "character varying(500)", maxLength: 500, nullable: true),
                    GraphJsonPath = table.Column<string>(type: "character varying(500)", maxLength: 500, nullable: true),
                    ReportPath = table.Column<string>(type: "character varying(500)", maxLength: 500, nullable: true),
                    ErrorMessage = table.Column<string>(type: "text", nullable: true),
                    StartedAt = table.Column<DateTime>(type: "timestamp with time zone", nullable: true),
                    CompletedAt = table.Column<DateTime>(type: "timestamp with time zone", nullable: true),
                    CreatedAt = table.Column<DateTime>(type: "timestamp with time zone", nullable: false),
                    UpdatedAt = table.Column<DateTime>(type: "timestamp with time zone", nullable: true),
                    DeletedAt = table.Column<DateTime>(type: "timestamp with time zone", nullable: true),
                    IsDeleted = table.Column<bool>(type: "boolean", nullable: false),
                    Version = table.Column<byte[]>(type: "bytea", rowVersion: true, nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_GraphifyArtifacts", x => x.Id);
                    table.ForeignKey(
                        name: "FK_GraphifyArtifacts_Repositories_RepositoryId",
                        column: x => x.RepositoryId,
                        principalTable: "Repositories",
                        principalColumn: "Id",
                        onDelete: ReferentialAction.Cascade);
                    table.ForeignKey(
                        name: "FK_GraphifyArtifacts_RepositoryBranches_RepositoryBranchId",
                        column: x => x.RepositoryBranchId,
                        principalTable: "RepositoryBranches",
                        principalColumn: "Id",
                        onDelete: ReferentialAction.Cascade);
                });

            migrationBuilder.CreateIndex(
                name: "IX_GraphifyArtifacts_RepositoryBranchId",
                table: "GraphifyArtifacts",
                column: "RepositoryBranchId",
                unique: true);

            migrationBuilder.CreateIndex(
                name: "IX_GraphifyArtifacts_RepositoryId",
                table: "GraphifyArtifacts",
                column: "RepositoryId");

            migrationBuilder.CreateIndex(
                name: "IX_GraphifyArtifacts_Status_CreatedAt",
                table: "GraphifyArtifacts",
                columns: new[] { "Status", "CreatedAt" });
        }

        /// <inheritdoc />
        protected override void Down(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.DropTable(
                name: "GraphifyArtifacts");
        }
    }
}
