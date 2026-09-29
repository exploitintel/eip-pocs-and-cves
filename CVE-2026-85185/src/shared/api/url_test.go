package api

import (
	"testing"

	"github.com/stretchr/testify/require"
)

func TestURL(t *testing.T) {
	u := NewURL()
	require.Empty(t, u.String())

	u.Path("1.0", "networks", "name-with-/-in-it")
	require.Equal(t, "/1.0/networks/name-with-%2F-in-it", u.String())

	u.Project("default")
	require.Equal(t, "/1.0/networks/name-with-%2F-in-it", u.String())

	u.Project("project-with-%-in-it")
	require.Equal(t, "/1.0/networks/name-with-%2F-in-it?project=project-with-%25-in-it", u.String())

	u.Project("another-project-with-%-in-it")
	require.Equal(t, "/1.0/networks/name-with-%2F-in-it?project=another-project-with-%25-in-it", u.String())

	u.Project("default")
	require.Equal(t, "/1.0/networks/name-with-%2F-in-it", u.String())

	u.Target("")
	require.Equal(t, "/1.0/networks/name-with-%2F-in-it", u.String())

	u.Target("member-with-%-in-it")
	require.Equal(t, "/1.0/networks/name-with-%2F-in-it?target=member-with-%25-in-it", u.String())

	u.Target("another-member-with-%-in-it")
	require.Equal(t, "/1.0/networks/name-with-%2F-in-it?target=another-member-with-%25-in-it", u.String())

	u.Target("none")
	require.Equal(t, "/1.0/networks/name-with-%2F-in-it", u.String())

	u.Host("example.com")
	require.Equal(t, "//example.com/1.0/networks/name-with-%2F-in-it", u.String())

	u.Scheme("https")
	require.Equal(t, "https://example.com/1.0/networks/name-with-%2F-in-it", u.String())

	// Output: /1.0/networks/name-with-%2F-in-it
	// /1.0/networks/name-with-%2F-in-it
	// /1.0/networks/name-with-%2F-in-it?project=project-with-%25-in-it
	// /1.0/networks/name-with-%2F-in-it?project=project-with-%25-in-it
	// /1.0/networks/name-with-%2F-in-it?project=project-with-%25-in-it&target=member-with-%25-in-it
	// //example.com/1.0/networks/name-with-%2F-in-it?project=project-with-%25-in-it&target=member-with-%25-in-it
	// https://example.com/1.0/networks/name-with-%2F-in-it?project=project-with-%25-in-it&target=member-with-%25-in-it
}
