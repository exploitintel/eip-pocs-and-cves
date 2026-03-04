/**
 * xrdp: A Remote Desktop Protocol server.
 *
 * Copyright (C) Jay Sorg 2004-2014
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * simple list
 */

#if defined(HAVE_CONFIG_H)
#include <config_ac.h>
#endif

#include "arch.h"
#include "os_calls.h"
#include "list16.h"

/*****************************************************************************/
struct list16 *
list16_create(void)
{
    struct list16 *self;

    self = (struct list16 *)g_malloc(sizeof(struct list16), 0);
    list16_init(self);
    return self;
}

/*****************************************************************************/
void
list16_delete(struct list16 *self)
{
    if (self == 0)
    {
        return;
    }

    list16_deinit(self);
    g_free(self);
}

/*****************************************************************************/
void
list16_init(struct list16 *self)
{
    g_memset(self, 0, sizeof(struct list16));
    self->max_count = 4;
    self->items = self->mitems;
}

/*****************************************************************************/
void
list16_deinit(struct list16 *self)
{
    if (self->items != self->mitems)
    {
        g_free(self->items);
        self->items = self->mitems; // Prevent double=free
    }
}

/*****************************************************************************/
/**
 * Makes the data array larger
 *
 * @param self The list
 * @return 1 for success, 0 for failure
 *
 * On failure, the data array is unchanged
 */
static int
expand_array(struct list16 *self)
{
    tui16 *p;
    int i;
    int new_max_count = self->max_count + 4;
    if (self->items == self->mitems)
    {
        /* Previous allocation is static. Make a new dynamic allocation */
        p = (tui16 *)malloc(sizeof(tui16) * new_max_count);
        if (p == NULL)
        {
            return 0;
        }
        g_memcpy(p, self->items, sizeof(tui16) * self->max_count);
    }
    else
    {
        /* Try to reallocate the existing array */
        p = (tui16 *)realloc(self->items, sizeof(tui16) * new_max_count);
        if (p == NULL)
        {
            return 0;
        }
    }

    /* Clear the new elements */
    for (i = self->max_count; i < new_max_count; ++i)
    {
        p[i] = 0;
    }

    self->max_count = new_max_count;
    self->items = p;

    return 1;
}

/*****************************************************************************/
int
list16_add_item(struct list16 *self, tui16 item)
{
    if (self->count >= self->max_count && !expand_array(self))
    {
        return 0;
    }

    self->items[self->count] = item;
    self->count++;
    return 1;
}

/*****************************************************************************/
tui16
list16_get_item(struct list16 *self, int index)
{
    if (index < 0 || index >= self->count)
    {
        return 0;
    }

    return self->items[index];
}

/*****************************************************************************/
void
list16_clear(struct list16 *self)
{
    if (self->items != self->mitems)
    {
        g_free(self->items);
    }
    self->count = 0;
    self->max_count = 4;
    self->items = self->mitems;
}

/*****************************************************************************/
int
list16_index_of(struct list16 *self, tui16 item)
{
    int i;

    for (i = 0; i < self->count; i++)
    {
        if (self->items[i] == item)
        {
            return i;
        }
    }

    return -1;
}

/*****************************************************************************/
void
list16_remove_item(struct list16 *self, int index)
{
    int i;

    if (index >= 0 && index < self->count)
    {
        for (i = index; i < (self->count - 1); i++)
        {
            self->items[i] = self->items[i + 1];
        }

        self->count--;
    }
}

/*****************************************************************************/
int
list16_insert_item(struct list16 *self, int index, tui16 item)
{
    /* Make sure there's at least one free element in the array */
    if (self->count >= self->max_count && !expand_array(self))
    {
        return 0;
    }

    if (index < self->count)
    {
        memmove(&self->items[index + 1], &self->items[index],
                (self->count - index) * sizeof(tui16));
    }

    self->items[index] = item;
    self->count++;
    return 1;
}
