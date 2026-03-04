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
 * @file common/set_int.h
 * @brief Handle a set of integer values (declarations)
  */

#ifndef SET_INT_H
#define SET_INT_H

#include "arch.h"

struct set_int;

/**
 * Construct a set capable of holding integer values
 *
 * At most one copy of each vlue will be held in the set
 *
 * @param min Lowest value which will be added to the set
 * @param max highest value which will be added to the set
 *
 * @return new set
 *
 * NULL is returned for no memory, or if min > max
 *
 * The set is initially empty
 */
struct set_int *
set_int_init(int min, int max);

/**
 * Destroy a set
 *
 * @param set set to destroy
 */
void
set_int_delete(struct set_int *set);

/**
 * Adds a value to a set
 *
 * @param set set
 * @param val value to add
 *
 * values outside of the initial max..max range will be silently ignored
 */
void
set_int_add(struct set_int *set, int val);


/**
 * Removes a value from a set
 *
 * @param set set
 * @param val value to remove
 *
 * It is not an error to remove a value which isn't in the set
 */
void
set_int_remove(struct set_int *set, int val);


/**
 * Tests whether a set contains a particular value
 *
 * @param set set
 * @param val value to test
 *
 * @return != 0 if the value is in the set
 */
int
set_int_contains(const struct set_int *set, int val);

/**
 * Adds all values in the range min..max to a set
 *
 * @param set set
 */
void
set_int_add_all(struct set_int *set);

/**
 * Removes all values in the range min..max from a set
 *
 * @param set set
 */
void
set_int_remove_all(struct set_int *set);

/**
 * Gets the next value from a set
 *
 * @param set set
 * @param[in,out] val Value to search from
 * @return != 0 if another value was found
 *
 * On success, the val parameter is replaced with the next highest
 * value from the set.
 */
int
set_int_get_next(const struct set_int *set, int *val);

#endif // SET_INT_H
