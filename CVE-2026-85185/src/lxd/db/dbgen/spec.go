package main

import (
	"strings"
	"text/template"
	"unicode"

	"github.com/canonical/lxd/lxd/db/query"
)

// Spec defines how an inspected struct maps to the database.
type Spec struct {
	Reference          *Spec
	ReferenceFieldName string
	StructName         string
	TableName          string
	Fields             []FieldSpec
	Joins              []string
}

func (s *Spec) hasPrimaryKey() bool {
	for _, f := range s.Fields {
		if f.Primary {
			return true
		}
	}

	return false
}

// unqualifiedColumnName returns the column name for the field spec without the `<table_name>.` prepended and boolean
// indicating if the column is defined on the spec table (e.g. it is not joined or coalesced). If the column is not
// defined on the spec table then an empty string is returned.
func (s *Spec) unqualifiedColumnName(field FieldSpec) (string, bool) {
	col, ok := strings.CutPrefix(field.ColumnName, s.TableName+".")
	if !ok {
		return "", false
	}

	return col, true
}

// FieldSpec is a simple mapping of struct field name to database column name.
type FieldSpec struct {
	FieldName  string
	ColumnName string
	SkipCreate bool
	SkipUpdate bool
	Primary    bool
}

// templateContext returns the values used for rendering the template.
func (s *Spec) templateContext() map[string]any {
	return map[string]any{
		"receiver":     string(s.receiver()),
		"structName":   s.StructName,
		"tableName":    s.TableName,
		"backtick":     "`",
		"scanColumns":  s.scanColumns(),
		"joins":        s.joins(),
		"scanArgs":     s.scanArgs(),
		"createValues": s.createValues(),
		"updateValues": s.updateValues(),
		"pkColumns":    s.pkColumns(),
		"pkValues":     s.pkValues(),
		"createStmt":   s.createStmt(),
		"updateStmt":   s.updateStmt(),
		"genAPIName":   s.Reference != nil,
		"apiName":      s.apiName(),
	}
}

var specSelectTemplate = template.Must(template.New("select").Parse(`
// TableName returns the table name for [{{ .structName }}] entities.
func ({{ .receiver }} {{ .structName }}) TableName() string {
	return "{{ .tableName }}"
}
{{ if .genAPIName }}
// APIName implements [query.APINamer] for API friendly error messages.
func ({{ .receiver }} {{ .structName }}) APIName() string {
	return {{ .apiName }}
}
{{ end }}
// SelectColumns returns a slice of column names for [{{ .structName }}] entities.
func ({{ .receiver }} {{ .structName }}) SelectColumns() []string {
	return {{ .scanColumns }}
}

// Joins returns a slice of join expressions for [{{ .structName }}].
func ({{ .receiver }} {{ .structName }}) Joins() []string {
	return {{ .joins }}
}

// ScanArgs implements [query.ScanArger] for [{{ .structName }}].
// This returns references to struct fields in definition order.
func ({{ .receiver }} *{{ .structName }}) ScanArgs() []any {
	return {{ .scanArgs }}
}
`))

var specExecTemplate = template.Must(template.New("exec").Parse(`
// CreateValues returns a list of values from [{{ .structName }}] entities matching the bind arguments in [CreateStmt].
func ({{ .receiver }} {{ .structName }}) CreateValues() []any {
	return {{ .createValues }}
}

// UpdateValues returns a list of values from [{{ .structName }}] entities matching the columns in [UpdateStmt].
func ({{ .receiver }} {{ .structName }}) UpdateValues() []any {
	return {{ .updateValues }}
}

// PKColumns returns the column names for the primary key of a [{{ .structName }}] entity used during an update.
// The returned slice must have the same number of elements as PKValues.
func ({{ .receiver }} {{ .structName }}) PKColumns() []string {
	return {{ .pkColumns }}
}

// PKValues returns the values for the primary key of a [{{ .structName }}] entity used during an update.
// The returned slice must have the same number of elements as PKColumns.
func ({{ .receiver }} {{ .structName }}) PKValues() []any {
	return {{ .pkValues }}
}

// CreateStmt returns a query that creates a [{{ .structName }}] entity.
func ({{ .receiver }} {{ .structName }}) CreateStmt() string {
	return "{{ .createStmt }}"
}

// UpdateStmt returns a query that updates a [{{ .structName }}] by primary key.
func ({{ .receiver }} {{ .structName }}) UpdateStmt() string {
	return "{{ .updateStmt }}"
}
`))

func (s *Spec) apiName() string {
	if s.Reference == nil {
		return ""
	}

	return string(s.receiver()) + "." + s.ReferenceFieldName + ".APIName()"
}

func (s *Spec) receiver() rune {
	return unicode.ToLower(rune(s.StructName[0]))
}

func (s *Spec) createStmt() string {
	cols := make([]string, 0, len(s.Fields))
	for _, f := range s.Fields {
		unqualifiedColName, ok := s.unqualifiedColumnName(f)
		if !ok || unqualifiedColName == columnID || f.SkipCreate {
			continue
		}

		cols = append(cols, unqualifiedColName)
	}

	return "INSERT INTO " + s.TableName + " (" + strings.Join(cols, ", ") + ") VALUES " + query.Params(len(cols))
}

func (s *Spec) updateStmt() string {
	cols := make([]string, 0, len(s.Fields))
	for _, f := range s.Fields {
		unqualifiedColName, ok := s.unqualifiedColumnName(f)
		if !ok || unqualifiedColName == columnID || f.SkipUpdate {
			continue
		}

		cols = append(cols, unqualifiedColName)
	}

	updates := make([]string, 0, len(cols))
	for _, col := range cols {
		updates = append(updates, col+" = ?")
	}

	return "UPDATE " + s.TableName + " SET " + strings.Join(updates, ", ") + " "
}

func (s *Spec) joins() string {
	if len(s.Joins) == 0 {
		return "[]string{}"
	}

	return "[]string{\n\t\t\"" + strings.Join(s.Joins, "\",\n\t\t\"") + "\",\n\t}"
}

func (s *Spec) scanColumns() string {
	cols := make([]string, 0, len(s.Fields))
	if s.Reference != nil {
		cols = make([]string, 0, len(s.Fields)+len(s.Reference.Fields))
		for _, f := range s.Reference.Fields {
			cols = append(cols, f.ColumnName)
		}
	}

	for _, f := range s.Fields {
		cols = append(cols, f.ColumnName)
	}

	return "[]string{\n\t\t\"" + strings.Join(cols, "\",\n\t\t\"") + "\",\n\t}"
}

func (s *Spec) createValues() string {
	values := make([]string, 0, len(s.Fields))
	for _, f := range s.Fields {
		unqualifiedColName, ok := s.unqualifiedColumnName(f)
		if !ok {
			continue
		}

		if unqualifiedColName == columnID || f.SkipCreate {
			continue
		}

		values = append(values, string(s.receiver())+"."+f.FieldName)
	}

	return "[]any{" + strings.Join(values, ", ") + "}"
}

func (s *Spec) updateValues() string {
	values := make([]string, 0, len(s.Fields))
	for _, f := range s.Fields {
		unqualifiedColName, ok := s.unqualifiedColumnName(f)
		if !ok {
			continue
		}

		if unqualifiedColName == columnID || f.SkipUpdate {
			continue
		}

		values = append(values, string(s.receiver())+"."+f.FieldName)
	}

	return "[]any{" + strings.Join(values, ", ") + "}"
}

func (s *Spec) pkColumns() string {
	cols := make([]string, 0, len(s.Fields))
	for _, f := range s.Fields {
		if f.Primary {
			cols = append(cols, strings.TrimPrefix(f.ColumnName, s.TableName+"."))
		}
	}

	return `[]string{"` + strings.Join(cols, `", "`) + `"}`
}

func (s *Spec) pkValues() string {
	values := make([]string, 0, len(s.Fields))
	for _, f := range s.Fields {
		if f.Primary {
			values = append(values, string(s.receiver())+"."+f.FieldName)
		}
	}

	return `[]any{` + strings.Join(values, ", ") + `}`
}

func (s *Spec) scanArgs() string {
	scanArgs := make([]string, 0, len(s.Fields))
	if s.Reference != nil {
		scanArgs = make([]string, 0, len(s.Reference.Fields)+len(s.Fields))
		for _, f := range s.Reference.Fields {
			scanArgs = append(scanArgs, "&"+string(s.receiver())+"."+s.ReferenceFieldName+"."+f.FieldName)
		}
	}

	for _, f := range s.Fields {
		scanArgs = append(scanArgs, "&"+string(s.receiver())+"."+f.FieldName)
	}

	return "[]any{" + strings.Join(scanArgs, ", ") + "}"
}
