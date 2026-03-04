/**
 * xrdp: A Remote Desktop Protocol server.
 *
 * Copyright (C) Jay Sorg 2004-2021
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
 */

/**
 * @file common/set_int.c
 * @brief Handle a set of integer values (definitions)
  */

#if defined(HAVE_CONFIG_H)
#include <config_ac.h>
#endif

#include <stddef.h>

#include "arch.h"
#include "set_int.h"
#include "log.h"

// Type to use for the bitmap in the set. This should be an
// optimum type for the platform, to improve search performance for
// sparse sets. The type must be unsigned for defined behaviour on
// right-shift.
typedef unsigned int word_type;
#define BITS_PER_WORD (sizeof(word_type) * 8)

struct set_int
{
    int min;
    int max;
    size_t word_count;
#ifdef __cplusplus
    word_type bits[1];
#else
    word_type bits[];
#endif
};

/*****************************************************************************/
struct set_int *
set_int_init(int min, int max)
{
    struct set_int *result = NULL;
    if (max < min)
    {
        LOG(LOG_LEVEL_ERROR, "Tried to create a set with max(%d) < min(%d)",
            max, min);
    }
    else
    {
        // Calculate the number of words needed for the bits;
        size_t word_count = (max - min) / BITS_PER_WORD + 1;

        result = (struct set_int *)malloc(
                     offsetof(struct set_int, bits) +
                     word_count * sizeof(word_type));
        if (result == NULL)
        {
            LOG(LOG_LEVEL_ERROR, "Out of memory constructing a set(%d, %d)",
                min, max);
        }
        else
        {
            result->min = min;
            result->max = max;
            result->word_count = word_count;
            set_int_remove_all(result);
        }
    }

    return result;
}

/*****************************************************************************/
void
set_int_delete(struct set_int *set)
{
    free(set);
}

/*****************************************************************************/
void
set_int_add(struct set_int *set, int val)
{
    if (set != NULL && val >= set->min && val <= set->max)
    {
        size_t index = (val - set->min) / BITS_PER_WORD;
        unsigned int bit = (val - set->min) % BITS_PER_WORD;
        set->bits[index] |= 1 << bit;
    }
}

/*****************************************************************************/
void
set_int_remove(struct set_int *set, int val)
{
    if (set != NULL && val >= set->min && val <= set->max)
    {
        size_t index = (val - set->min) / BITS_PER_WORD;
        unsigned int bit = (val - set->min) % BITS_PER_WORD;
        set->bits[index] &= ~(1 << bit);
    }
}

/*****************************************************************************/
int
set_int_contains(const struct set_int *set, int val)
{
    int result = 0;
    if (set != NULL && val >= set->min && val <= set->max)
    {
        size_t index = (val - set->min) / BITS_PER_WORD;
        unsigned int bit = (val - set->min) % BITS_PER_WORD;
        result = (set->bits[index] >> bit) & 1;
    }

    return result;
}

/*****************************************************************************/
void
set_int_add_all(struct set_int *set)
{
    if (set != NULL)
    {
        memset(set->bits, 0xff, set->word_count * sizeof(set->bits[0]));
    }
}

/*****************************************************************************/
void
set_int_remove_all(struct set_int *set)
{
    if (set != NULL)
    {
        memset(set->bits, 0, set->word_count * sizeof(set->bits[0]));
    }
}

/*****************************************************************************/
int
set_int_get_next(const struct set_int *set, int *val)
{
    // Sanity checks
    if (set == NULL || *val >= set->max)
    {
        return 0;
    }

    // Work out the next likely value
    int next = (*val < set->min) ? set->min : (*val) + 1;

    // Convert that to an index and bit
    size_t index = (next - set->min) / BITS_PER_WORD;
    unsigned int bit = (next - set->min) % BITS_PER_WORD;

    // Any bits left in the current word?
    word_type w = set->bits[index] >> bit;
    if (w == 0)
    {
        // Look for the next word with set bits
        do
        {
            ++index;
            if (index >= set->word_count)
            {
                return 0;
            }
        }
        while (set->bits[index] == 0);

        w = set->bits[index];
        bit = 0;
    }

    // If we get here, w is guaranteed to have at least one set bit
    while ((w & 1) == 0)
    {
        w = w >> 1;
        ++bit;
    }

    // Now the index and bit are pointing to the
    // next set bit in the bitmap
    next = index * BITS_PER_WORD + bit + set->min; // Turn back into a value
    int result = (next <= set->max); // Must be in range.
    if (result)
    {
        *val = next;
    }
    return result;;
}
