using System;
using Microsoft.EntityFrameworkCore.Infrastructure;
using Microsoft.EntityFrameworkCore.Migrations;

#nullable disable

namespace OpenDeepWiki.Sqlite.Migrations
{
    [DbContext(typeof(SqliteDbContext))]
    [Migration("20260704090000_AddBranchGenerationTasks")]
    public partial class AddBranchGenerationTasks : Migration
    {
        protected override void Up(MigrationBuilder migrationBuilder)
        {
            migrationBuilder.AddColumn<string>(name: "BranchId", table: "RepositoryProcessingLogs", type: "TEXT", maxLength: 36, nullable: true);
            migrationBuilder.AddColumn<string>(name: "GenerationTaskId", table: "RepositoryProcessingLogs", type: "TEXT", maxLength: 36, nullable: true);
            migrationBuilder.AddColumn<int>(name: "GenerationStatus", table: "RepositoryBranches", type: "INTEGER", nullable: true);
            migrationBuilder.AddColumn<DateTime>(name: "LastGenerationCompletedAt", table: "RepositoryBranches", type: "TEXT", nullable: true);
            migrationBuilder.AddColumn<string>(name: "LastGenerationError", table: "RepositoryBranches", type: "TEXT", nullable: true);
            migrationBuilder.AddColumn<DateTime>(name: "LastGenerationStartedAt", table: "RepositoryBranches", type: "TEXT", nullable: true);
            migrationBuilder.AddColumn<string>(name: "LastGenerationTaskId", table: "RepositoryBranches", type: "TEXT", maxLength: 36, nullable: true);

            migrationBuilder.CreateTable(
                name: "BranchGenerationTasks",
                columns: table => new
                {
                    Id = table.Column<string>(type: "TEXT", maxLength: 36, nullable: false),
                    RepositoryId = table.Column<string>(type: "TEXT", maxLength: 36, nullable: false),
                    BranchId = table.Column<string>(type: "TEXT", maxLength: 36, nullable: false),
                    Status = table.Column<int>(type: "INTEGER", nullable: false),
                    Mode = table.Column<int>(type: "INTEGER", nullable: false),
                    Priority = table.Column<int>(type: "INTEGER", nullable: false),
                    IsManualTrigger = table.Column<bool>(type: "INTEGER", nullable: false),
                    RetryCount = table.Column<int>(type: "INTEGER", nullable: false),
                    ErrorMessage = table.Column<string>(type: "TEXT", nullable: true),
                    StartedAt = table.Column<DateTime>(type: "TEXT", nullable: true),
                    CompletedAt = table.Column<DateTime>(type: "TEXT", nullable: true),
                    RequestedBy = table.Column<string>(type: "TEXT", maxLength: 36, nullable: true),
                    TargetCommitId = table.Column<string>(type: "TEXT", maxLength: 40, nullable: true),
                    CreatedAt = table.Column<DateTime>(type: "TEXT", nullable: false),
                    UpdatedAt = table.Column<DateTime>(type: "TEXT", nullable: true),
                    DeletedAt = table.Column<DateTime>(type: "TEXT", nullable: true),
                    IsDeleted = table.Column<bool>(type: "INTEGER", nullable: false),
                    Version = table.Column<byte[]>(type: "BLOB", rowVersion: true, nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_BranchGenerationTasks", x => x.Id);
                    table.ForeignKey(name: "FK_BranchGenerationTasks_Repositories_RepositoryId", column: x => x.RepositoryId, principalTable: "Repositories", principalColumn: "Id", onDelete: ReferentialAction.Cascade);
                    table.ForeignKey(name: "FK_BranchGenerationTasks_RepositoryBranches_BranchId", column: x => x.BranchId, principalTable: "RepositoryBranches", principalColumn: "Id", onDelete: ReferentialAction.Cascade);
                });

            migrationBuilder.CreateTable(
                name: "RepositoryGenerationLocks",
                columns: table => new
                {
                    Id = table.Column<string>(type: "TEXT", maxLength: 36, nullable: false),
                    RepositoryId = table.Column<string>(type: "TEXT", maxLength: 36, nullable: false),
                    OwnerType = table.Column<int>(type: "INTEGER", nullable: false),
                    OwnerId = table.Column<string>(type: "TEXT", maxLength: 36, nullable: false),
                    Scope = table.Column<int>(type: "INTEGER", nullable: false),
                    AcquiredAt = table.Column<DateTime>(type: "TEXT", nullable: false),
                    CreatedAt = table.Column<DateTime>(type: "TEXT", nullable: false),
                    UpdatedAt = table.Column<DateTime>(type: "TEXT", nullable: true),
                    DeletedAt = table.Column<DateTime>(type: "TEXT", nullable: true),
                    IsDeleted = table.Column<bool>(type: "INTEGER", nullable: false),
                    Version = table.Column<byte[]>(type: "BLOB", rowVersion: true, nullable: true)
                },
                constraints: table =>
                {
                    table.PrimaryKey("PK_RepositoryGenerationLocks", x => x.Id);
                    table.ForeignKey(name: "FK_RepositoryGenerationLocks_Repositories_RepositoryId", column: x => x.RepositoryId, principalTable: "Repositories", principalColumn: "Id", onDelete: ReferentialAction.Cascade);
                });

            migrationBuilder.CreateIndex(name: "IX_RepositoryProcessingLogs_RepositoryId_BranchId_GenerationTaskId_CreatedAt", table: "RepositoryProcessingLogs", columns: new[] { "RepositoryId", "BranchId", "GenerationTaskId", "CreatedAt" });
            migrationBuilder.CreateIndex(name: "IX_RepositoryProcessingLogs_BranchId", table: "RepositoryProcessingLogs", column: "BranchId");
            migrationBuilder.CreateIndex(name: "IX_RepositoryProcessingLogs_GenerationTaskId", table: "RepositoryProcessingLogs", column: "GenerationTaskId");
            migrationBuilder.CreateIndex(name: "IX_BranchGenerationTasks_BranchId_Status", table: "BranchGenerationTasks", columns: new[] { "BranchId", "Status" });
            migrationBuilder.CreateIndex(name: "IX_BranchGenerationTasks_BranchId_Status_Mode", table: "BranchGenerationTasks", columns: new[] { "BranchId", "Status", "Mode" }, unique: true, filter: "\"Status\" IN (0, 1)");
            migrationBuilder.CreateIndex(name: "IX_BranchGenerationTasks_RepositoryId_Status", table: "BranchGenerationTasks", columns: new[] { "RepositoryId", "Status" });
            migrationBuilder.CreateIndex(name: "IX_BranchGenerationTasks_Status_Priority_CreatedAt", table: "BranchGenerationTasks", columns: new[] { "Status", "Priority", "CreatedAt" });
            migrationBuilder.CreateIndex(name: "IX_RepositoryGenerationLocks_RepositoryId", table: "RepositoryGenerationLocks", column: "RepositoryId", unique: true);
            migrationBuilder.AddForeignKey(name: "FK_RepositoryProcessingLogs_BranchGenerationTasks_GenerationTaskId", table: "RepositoryProcessingLogs", column: "GenerationTaskId", principalTable: "BranchGenerationTasks", principalColumn: "Id");
            migrationBuilder.AddForeignKey(name: "FK_RepositoryProcessingLogs_RepositoryBranches_BranchId", table: "RepositoryProcessingLogs", column: "BranchId", principalTable: "RepositoryBranches", principalColumn: "Id");
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
