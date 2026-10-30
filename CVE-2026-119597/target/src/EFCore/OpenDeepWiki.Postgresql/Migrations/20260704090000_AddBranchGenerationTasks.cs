using System;
using Microsoft.EntityFrameworkCore.Infrastructure;
using Microsoft.EntityFrameworkCore.Migrations;

#nullable disable

namespace OpenDeepWiki.Postgresql.Migrations
{
    [DbContext(typeof(PostgresqlDbContext))]
    [Migration("20260704090000_AddBranchGenerationTasks")]
    public partial class AddBranchGenerationTasks : Migration
    {
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.AddColumn<string>(
                name: "BranchId",
                table: "RepositoryProcessingLogs",
                type: "character varying(36)",
                maxLength: 36,
                nullable: true);

            migrationBuilder.AddColumn<string>(
                name: "GenerationTaskId",
                table: "RepositoryProcessingLogs",
                type: "character varying(36)",
                maxLength: 36,
                nullable: true);

            migrationBuilder.AddColumn<int>(
                name: "GenerationStatus",
                table: "RepositoryBranches",
                type: "integer",
                nullable: true);

            migrationBuilder.AddColumn<DateTime>(
                name: "LastGenerationCompletedAt",
                table: "RepositoryBranches",
                type: "timestamp with time zone",
                nullable: true);

            migrationBuilder.AddColumn<string>(
                name: "LastGenerationError",
                table: "RepositoryBranches",
                type: "text",
                nullable: true);

            migrationBuilder.AddColumn<DateTime>(
                name: "LastGenerationStartedAt",
                table: "RepositoryBranches",
                type: "timestamp with time zone",
                nullable: true);

            migrationBuilder.AddColumn<string>(
                name: "LastGenerationTaskId",
                table: "RepositoryBranches",
                type: "character varying(36)",
                maxLength: 36,
                nullable: true);

            migrationBuilder.CreateTable(
                name: "BranchGenerationTasks",
                columns: table => new
                {
                    Id = table.Column<string>(type: "character varying(36)", maxLength: 36, nullable: false),
                    RepositoryId = table.Column<string>(type: "character varying(36)", maxLength: 36, nullable: false),
                    BranchId = table.Column<string>(type: "character varying(36)", maxLength: 36, nullable: false),
                    Status = table.Column<int>(type: "integer", nullable: false),
                    Mode = table.Column<int>(type: "integer", nullable: false),
                    Priority = table.Column<int>(type: "integer", nullable: false),
                    IsManualTrigger = table.Column<bool>(type: "boolean", nullable: false),
                    RetryCount = table.Column<int>(type: "integer", nullable: false),
                    ErrorMessage = table.Column<string>(type: "text", nullable: true),
                    StartedAt = table.Column<DateTime>(type: "timestamp with time zone", nullable: true),
                    CompletedAt = table.Column<DateTime>(type: "timestamp with time zone", nullable: true),
                    RequestedBy = table.Column<string>(type: "character varying(36)", maxLength: 36, nullable: true),
                    TargetCommitId = table.Column<string>(type: "character varying(40)", maxLength: 40, nullable: true),
                    CreatedAt = table.Column<DateTime>(type: "timestamp with time zone", nullable: false),
                    UpdatedAt = table.Column<DateTime>(type: "timestamp with time zone", nullable: true),
                    DeletedAt = table.Column<DateTime>(type: "timestamp with time zone", nullable: true),
                    IsDeleted = table.Column<bool>(type: "boolean", nullable: false),
                    Version = table.Column<byte[]>(type: "bytea", rowVersion: true, nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_BranchGenerationTasks", x => x.Id);
                    table.ForeignKey(
                        name: "FK_BranchGenerationTasks_Repositories_RepositoryId",
                        column: x => x.RepositoryId,
                        principalTable: "Repositories",
                        principalColumn: "Id",
                        onDelete: ReferentialAction.Cascade);
                    table.ForeignKey(
                        name: "FK_BranchGenerationTasks_RepositoryBranches_BranchId",
                        column: x => x.BranchId,
                        principalTable: "RepositoryBranches",
                        principalColumn: "Id",
                        onDelete: ReferentialAction.Cascade);
                });

            migrationBuilder.CreateTable(
                name: "RepositoryGenerationLocks",
                columns: table => new
                {
                    Id = table.Column<string>(type: "character varying(36)", maxLength: 36, nullable: false),
                    RepositoryId = table.Column<string>(type: "character varying(36)", maxLength: 36, nullable: false),
                    OwnerType = table.Column<int>(type: "integer", nullable: false),
                    OwnerId = table.Column<string>(type: "character varying(36)", maxLength: 36, nullable: false),
                    Scope = table.Column<int>(type: "integer", nullable: false),
                    AcquiredAt = table.Column<DateTime>(type: "timestamp with time zone", nullable: false),
                    CreatedAt = table.Column<DateTime>(type: "timestamp with time zone", nullable: false),
                    UpdatedAt = table.Column<DateTime>(type: "timestamp with time zone", nullable: true),
                    DeletedAt = table.Column<DateTime>(type: "timestamp with time zone", nullable: true),
                    IsDeleted = table.Column<bool>(type: "boolean", nullable: false),
                    Version = table.Column<byte[]>(type: "bytea", rowVersion: true, nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_RepositoryGenerationLocks", x => x.Id);
                    table.ForeignKey(
                        name: "FK_RepositoryGenerationLocks_Repositories_RepositoryId",
                        column: x => x.RepositoryId,
                        principalTable: "Repositories",
                        principalColumn: "Id",
                        onDelete: ReferentialAction.Cascade);
                });

            migrationBuilder.CreateIndex(
                name: "IX_RepositoryProcessingLogs_RepositoryId_BranchId_GenerationTaskId_CreatedAt",
                table: "RepositoryProcessingLogs",
                columns: new[] { "RepositoryId", "BranchId", "GenerationTaskId", "CreatedAt" });

            migrationBuilder.CreateIndex(
                name: "IX_RepositoryProcessingLogs_BranchId",
                table: "RepositoryProcessingLogs",
                column: "BranchId");

            migrationBuilder.CreateIndex(
                name: "IX_RepositoryProcessingLogs_GenerationTaskId",
                table: "RepositoryProcessingLogs",
                column: "GenerationTaskId");

            migrationBuilder.CreateIndex(
                name: "IX_BranchGenerationTasks_BranchId_Status",
                table: "BranchGenerationTasks",
                columns: new[] { "BranchId", "Status" });

            migrationBuilder.CreateIndex(
                name: "IX_BranchGenerationTasks_BranchId_Status_Mode",
                table: "BranchGenerationTasks",
                columns: new[] { "BranchId", "Status", "Mode" },
                unique: true,
                filter: "\"Status\" IN (0, 1)");

            migrationBuilder.CreateIndex(
                name: "IX_BranchGenerationTasks_RepositoryId_Status",
                table: "BranchGenerationTasks",
                columns: new[] { "RepositoryId", "Status" });

            migrationBuilder.CreateIndex(
                name: "IX_BranchGenerationTasks_Status_Priority_CreatedAt",
                table: "BranchGenerationTasks",
                columns: new[] { "Status", "Priority", "CreatedAt" });

            migrationBuilder.CreateIndex(
                name: "IX_RepositoryGenerationLocks_RepositoryId",
                table: "RepositoryGenerationLocks",
                column: "RepositoryId",
                unique: true);

            migrationBuilder.AddForeignKey(
                name: "FK_RepositoryProcessingLogs_BranchGenerationTasks_GenerationTaskId",
                table: "RepositoryProcessingLogs",
                column: "GenerationTaskId",
                principalTable: "BranchGenerationTasks",
                principalColumn: "Id");

            migrationBuilder.AddForeignKey(
                name: "FK_RepositoryProcessingLogs_RepositoryBranches_BranchId",
                table: "RepositoryProcessingLogs",
                column: "BranchId",
                principalTable: "RepositoryBranches",
                principalColumn: "Id");
        }

        protected override void Down(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.DropForeignKey(name: "FK_RepositoryProcessingLogs_BranchGenerationTasks_GenerationTaskId", table: "RepositoryProcessingLogs");
            migrationBuilder.DropForeignKey(name: "FK_RepositoryProcessingLogs_RepositoryBranches_BranchId", table: "RepositoryProcessingLogs");
            migrationBuilder.DropTable(name: "RepositoryGenerationLocks");
            migrationBuilder.DropTable(name: "BranchGenerationTasks");
            migrationBuilder.DropIndex(name: "IX_RepositoryProcessingLogs_RepositoryId_BranchId_GenerationTaskId_CreatedAt", table: "RepositoryProcessingLogs");
            migrationBuilder.DropIndex(name: "IX_RepositoryProcessingLogs_BranchId", table: "RepositoryProcessingLogs");
            migrationBuilder.DropIndex(name: "IX_RepositoryProcessingLogs_GenerationTaskId", table: "RepositoryProcessingLogs");
            migrationBuilder.DropColumn(name: "BranchId", table: "RepositoryProcessingLogs");
            migrationBuilder.DropColumn(name: "GenerationTaskId", table: "RepositoryProcessingLogs");
            migrationBuilder.DropColumn(name: "GenerationStatus", table: "RepositoryBranches");
            migrationBuilder.DropColumn(name: "LastGenerationCompletedAt", table: "RepositoryBranches");
            migrationBuilder.DropColumn(name: "LastGenerationError", table: "RepositoryBranches");
            migrationBuilder.DropColumn(name: "LastGenerationStartedAt", table: "RepositoryBranches");
            migrationBuilder.DropColumn(name: "LastGenerationTaskId", table: "RepositoryBranches");
        }
    }
}
